<?php
namespace FlySkyPie\CloudCatapult;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class AuthorizationWizard
{
  function __construct( ) 
  {
    if (php_sapi_name() != 'cli') {
      throw new Exception('This application must be run on the command line.');
    }
  }
  
  public static function start()
  {
    $credential_path = getenv('OAUTH_CREDENTIALS_PATH')."/credentials.json";

    $client = new Google_Client();
    $client->setApplicationName('Cloud Catapult');
    $client->setScopes(Google_Service_Drive::DRIVE_FILE);
    $client->setAuthConfig( $credential_path );
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    self::getToken($client);
    
    self::createRootFolder($client);
  }
  
  private function createRootFolder( $client )
  {
    $service = new Google_Service_Drive( $client );
    $fileMetadata = new Google_Service_Drive_DriveFile([
      'name' => 'Cloud Catapult Target',
      'mimeType' => 'application/vnd.google-apps.folder']);
    $file = $service->files->create($fileMetadata,['fields' => 'id']);
    printf("Folder ID: %s\n", $file->id);
  }
  
  private function getToken( $client )
  {
    // Load previously authorized token from a file, if it exists.
    $token_path =   getenv('OAUTH_TOKEN_PATH')."/token.json";
    if ( file_exists($token_path) ) {
      $accessToken = json_decode(file_get_contents($token_path), true);
      $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if (!$client->isAccessTokenExpired()) {
      return;
    }

    // Refresh the token if possible, else fetch a new one.
    if ($client->getRefreshToken()) {
      $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    } else {
      self::requestNewToken($client);
    }
    self::saveToken($client->getAccessToken(),$token_path);
  }
  
  /*
   * @todo Request authorization from the user.
   */
  private function requestNewToken($client)
  {
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
    $client->setAccessToken($accessToken);

    // Check to see if there was an error.
    if (array_key_exists('error', $accessToken)) {
      throw new Exception(join(', ', $accessToken));
    }
  }
  
  /*
   * @todo Save the token to a file.
   * @param String $Token
   * @param String $TokenPath
   */
  private function saveToken($Token, $TokenPath)
  {
    // 
    if (!file_exists(dirname($TokenPath))) {
      mkdir(dirname($TokenPath), 0700, true);
    }
    file_put_contents($TokenPath, json_encode( $Token ));

  }
}