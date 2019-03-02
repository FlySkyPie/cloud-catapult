<?php

namespace FlySkyPie\CloudCatapult;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;

class Catapult {

  private $GoogleClient;
  private $GoogleService;
  private $TargetFolderId;

  function __construct() {
    $this->GoogleClient = $this->getGoogleClient();
    $this->GoogleService = new Google_Service_Drive($this->GoogleClient);
    $this->TargetFolderId = getenv('CLOUD_TARGET_ID');
  }

  /*
   * @todo get Google_Client
   * @var Google_Client
   */

  private function getGoogleClient() {
    $CredentialPath = getenv('OAUTH_CREDENTIALS_PATH') . "/credentials.json";

    //create google client object
    $Client = new Google_Client();
    $Client->setApplicationName('Grive Backup');
    $Client->setScopes(Google_Service_Drive::DRIVE_FILE);
    $Client->setAuthConfig($CredentialPath);
    $Client->setAccessType('offline');
    $Client->setPrompt('select_account consent');

    $this->getGoogleToken($Client);

    return $Client;
  }

  /*
   * @todo check and get token for Google_Client
   * @param Google_Client $Client
   */

  private function getGoogleToken($Client) {
    $TokenPath = getenv('OAUTH_TOKEN_PATH') . "/token.json";

    //check token file exists
    if (!file_exists($TokenPath)) {
      throw new Exception("The token file do not exists.");
    }
    $AccessToken = json_decode(file_get_contents($TokenPath), true);
    $Client->setAccessToken($AccessToken);

    // If there is no previous token or it's expired.
    if ($Client->isAccessTokenExpired()) {
      if ($Client->getRefreshToken()) {
        $Client->fetchAccessTokenWithRefreshToken($Client->getRefreshToken());
      } else {
        throw new Exception("The token was expired, and refresh had failed.");
      }
    }
  }

  /*
   * @todo upload a file to the cloud
   * @param Array $FileINFO
   * @var String
   */

  public function shoot($FileINFO) {
    $FileName = $FileINFO['name'];
    $FileSize = $FileINFO['size'];
    $FilePath = $FileINFO['tmp_name'];

    //There are not file been selected or file are empty.
    if ($FileSize === 0) {
      return "";
    }

    $DriveFile = new Google_Service_Drive_DriveFile();
    $DriveFile->setName($FileName);
    $DriveFile->setParents([$this->TargetFolderId]);
    $Query = ['data' => file_get_contents($FilePath),
        'mimeType' => 'application/octet-stream',
        'uploadType' => 'media'
    ];
    $Result = $this->GoogleService->files->create($DriveFile, $Query);
    $this->shareFile($Result->id);
    return $Result->id;
  }

  /*
   * @todo set file permission to public read only
   * @param String $FileId
   */

  private function shareFile($FileId) {
    try {
      $NewPermission = new Google_Service_Drive_Permission();
      $NewPermission->setType('anyone');
      $NewPermission->setRole('reader');
      $this->GoogleService->permissions->create($FileId, $NewPermission);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

}
