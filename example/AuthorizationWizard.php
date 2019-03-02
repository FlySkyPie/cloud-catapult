<?php
require __DIR__ . '/../vendor/autoload.php';

use FlySkyPie\CloudCatapult\AuthorizationWizard;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->overload();

AuthorizationWizard::start();
