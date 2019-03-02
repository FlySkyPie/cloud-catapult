<?php

namespace FlySkyPie\CloudCatapult;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class AuthorizationWizard {

  function __construct() {
    if (php_sapi_name() != 'cli') {
      throw new Exception('This application must be run on the command line.');
    }
  }

  /*
   * @todo start the wizard that get authorization from Google
   */

  public static function start() {
    $CredentialPath = getenv('OAUTH_CREDENTIALS_PATH') . "/credentials.json";

    $Client = new Google_Client();
    $Client->setApplicationName('Cloud Catapult');
    $Client->setScopes(Google_Service_Drive::DRIVE_FILE);
    $Client->setAuthConfig($CredentialPath);
    $Client->setAccessType('offline');
    $Client->setPrompt('select_account consent');

    self::getToken($Client);

    if (empty(getenv('CLOUD_TARGET_ID'))) {
      self::createRootFolder($Client);
    }
  }

  /*
   * @todo create a folder as catapult target
   * @param Google_Client $Client
   */

  private function createRootFolder($Client) {
    $GoogleService = new Google_Service_Drive($Client);
    $FileMetadata = new Google_Service_Drive_DriveFile([
        'name' => 'Cloud Catapult Target',
        'mimeType' => 'application/vnd.google-apps.folder']);
    $file = $GoogleService->files->create($FileMetadata, ['fields' => 'id']);
    printf("please copy the line under below to .env :\n%s\n", 'CLOUD_TARGET_ID="' . $file->id . '"');
  }

  /*
   * @todo get authorization token from Google
   * @param Google_Client $Client
   */

  private function getToken($Client) {
    // Load previously authorized token from a file, if it exists.
    $TokenPath = getenv('OAUTH_TOKEN_PATH') . "/token.json";
    if (file_exists($TokenPath)) {
      $AccessToken = json_decode(file_get_contents($TokenPath), true);
      $Client->setAccessToken($AccessToken);
    }

    // If there is no previous token or it's expired.
    if (!$Client->isAccessTokenExpired()) {
      return;
    }

    // Refresh the token if possible, else fetch a new one.
    if ($Client->getRefreshToken()) {
      $Client->fetchAccessTokenWithRefreshToken($Client->getRefreshToken());
    } else {
      self::requestNewToken($Client);
    }
    self::saveToken($Client->getAccessToken(), $TokenPath);
  }

  /*
   * @todo Request authorization from the user.
   * @param Google_Client $Client
   */

  private function requestNewToken($Client) {
    $AuthUrl = $Client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $AuthUrl);
    print 'Enter verification code: ';
    $AuthCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $AccessToken = $Client->fetchAccessTokenWithAuthCode($AuthCode);
    $Client->setAccessToken($AccessToken);

    // Check to see if there was an error.
    if (array_key_exists('error', $AccessToken)) {
      throw new Exception(join(', ', $AccessToken));
    }
  }

  /*
   * @todo Save the token to a file.
   * @param String $Token
   * @param String $TokenPath
   */

  private function saveToken($Token, $TokenPath) {
    // 
    if (!file_exists(dirname($TokenPath))) {
      mkdir(dirname($TokenPath), 0700, true);
    }
    file_put_contents($TokenPath, json_encode($Token));
  }

}
