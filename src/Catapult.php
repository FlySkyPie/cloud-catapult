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
    $credential_path = getenv('OAUTH_CREDENTIALS_PATH') . "/credentials.json";

    //create google client object
    $client = new Google_Client();
    $client->setApplicationName('Grive Backup');
    $client->setScopes(Google_Service_Drive::DRIVE_FILE);
    $client->setAuthConfig($credential_path);
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    $this->getGoogleToken($client);

    return $client;
  }

  /*
   * @todo check and get token for Google_Client
   * @param Google_Client $client
   */

  private function getGoogleToken($client) {
    $token_path = getenv('OAUTH_TOKEN_PATH') . "/token.json";

    //check token file exists
    if (!file_exists($token_path)) {
      throw new Exception("The token file do not exists.");
    }
    $accessToken = json_decode(file_get_contents($token_path), true);
    $client->setAccessToken($accessToken);

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
      if ($client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
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
    $errors = array();
    $file_name = $FileINFO['name'];
    $file_size = $FileINFO['size'];
    $file_tmp = $FileINFO['tmp_name'];
    $file_type = $FileINFO['type'];

    //There are not file been selected or file are empty.
    if ($file_size === 0) {
      return "";
    }

    $TargetFolderId = getenv('CLOUD_TARGET_ID');
    $DriveFile = new Google_Service_Drive_DriveFile();
    $DriveFile->setName($file_name);
    $DriveFile->setParents([$TargetFolderId]);
    $query = ['data' => file_get_contents($file_tmp),
        'mimeType' => 'application/octet-stream',
        'uploadType' => 'media'
    ];
    $result = $this->GoogleService->files->create($DriveFile, $query);
    $this->shareFile($result->id);
    return $result->id;
  }

  private function shareFile($FileId) {
    try {
      $newPermission = new Google_Service_Drive_Permission();
      $newPermission->setType('anyone');
      $newPermission->setRole('reader');
      $this->GoogleService->permissions->create($FileId, $newPermission);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

}
