<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require '../vendor/autoload.php';

$client = new Google_Client();

$config = require 'config.php';

$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);

$client->setRedirectUri("http://localhost/FileSheild/backend-php/googlecallback.php");

$client->addScope("email");
$client->addScope("profile");

$login_url = $client->createAuthUrl();

/* redirect to Google */

header("Location: ".$login_url);
exit;