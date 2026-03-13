<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require '../vendor/autoload.php';

$conn = new mysqli("localhost","root","","FileSheild");

if($conn->connect_error){
    die("Database connection failed");
}

$client = new Google_Client();

$config = require 'config.php';

$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);
$client->setRedirectUri("http://localhost/FileSheild/backend-php/googlecallback.php");

if(isset($_GET['code'])){

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if(isset($token['error'])){
    die("OAuth failed");
}

$client->setAccessToken($token);

$service = new Google_Service_Oauth2($client);
$user = $service->userinfo->get();

$google_id = $user->id;
$email = $user->email;
$name = $user->name;

$first = explode(" ",$name)[0];
$last = explode(" ",$name)[1] ?? "";


/* Check if user already exists */

$stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR google_id=?");
$stmt->bind_param("ss",$email,$google_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){

    /* Create new Google user */

    $provider = "google";
    $password = NULL;
    $is_verified = 1;

    $stmt = $conn->prepare("
        INSERT INTO users(first_name,last_name,email,password,google_id,provider,is_verified)
        VALUES(?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "ssssssi",
        $first,
        $last,
        $email,
        $password,
        $google_id,
        $provider,
        $is_verified
    );

    $stmt->execute();

    /* fetch generated user id */

    $getUser = $conn->prepare("SELECT id FROM users WHERE email=?");
    $getUser->bind_param("s",$email);
    $getUser->execute();
    $userId = $getUser->get_result()->fetch_assoc()['id'];

}else{

    $row = $result->fetch_assoc();
    $userId = $row['id'];

    /* ensure google id linked */

    $updateGoogle = $conn->prepare("
        UPDATE users
        SET google_id=?, provider='google', is_verified=1
        WHERE id=?
    ");

    $updateGoogle->bind_param("ss",$google_id,$userId);
    $updateGoogle->execute();
}


/* Generate auth token */

$auth_token = bin2hex(random_bytes(32));

$update = $conn->prepare("UPDATE users SET auth_token=? WHERE id=?");
$update->bind_param("ss",$auth_token,$userId);
$update->execute();


/* Redirect with token */

header("Location: /FileSheild/frontend/google-success.html?token=".$auth_token
."&first=".$first
."&last=".$last
."&email=".$email
."&verified=1");
exit;

}