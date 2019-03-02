<?php

require __DIR__ . '/../vendor/autoload.php';

use FlySkyPie\CloudCatapult\Catapult;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
$dotenv->required('CLOUD_TARGET_ID');

if (!isset($_FILES['fileToUpload'])) {
  exit();
}

$Catapult = new Catapult();
$FileId = $Catapult->shoot($_FILES['fileToUpload']);
if (!empty($FileId)) {
  $url = "https://drive.google.com/uc?export=download&id=$FileId";
  echo '<a href="' . $url . '">Download the file you uploaded from Google drive</a>';
}

 