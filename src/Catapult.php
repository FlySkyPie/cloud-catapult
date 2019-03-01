<?php
namespace FlySkyPie\CloudCatapult;

class Catapult
{
  private $GoogleClient;
  private $GoogleService;
  private $TargetFolderId;

  function __construct( ) 
  {
    $this->GoogleClient =  $this->getGoogleClient();
    $this->GoogleService = new Google_Service_Drive( $this->GoogleClient );
    $this->TargetFolderId =  env('CLOUD_TARGET_ID');
  }
  
  /*
   * @todo get Google_Client
   * @var Google_Client
   */
  private function getGoogleClient()
  {
    $credential_path = env('OAUTH_CREDENTIALS_PATH')."/credentials.json";

    //create google client object
    $client = new Google_Client();
    $client->setApplicationName('Grive Backup');
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAuthConfig( $credential_path );
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
    
    $this->getGoogleToken($client);

    return $client;
  }
  
  /*
   * @todo check and get token for Google_Client
   * @param Google_Client $client
   */
  function getGoogleToken( $client )
  {
    $token_path =   env('OAUTH_TOKEN_PATH')."/token.json";
    
    //check token file exists
    if(!file_exists($token_path)){
        throw new Exception("The token was expired.");
    }
    $accessToken = json_decode(file_get_contents($token_path), true);
    $client->setAccessToken($accessToken);
    
    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        }
        else{
            throw new Exception("The token was expired, and refresh had failed.");
        }
    }
  }
  
  /*
   * @todo uplode a file to the cloud
   * @param String $File
   * @var String
   */
  function upload( $File )
  {
    $file = new Google_Service_Drive_DriveFile();
    $result =  $this->GoogleService->files->insert($file, array(
      'data' => $File,
      'mimeType' => 'application/octet-stream',
      'uploadType' => 'media'
    ));
    return $result->id;
  }
}