<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
require 'mailer.php'; //  ADD THIS

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

    // 🔍 Check user
    $stmt = $conn->prepare("SELECT id, provider FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){

        //  NEW USER
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

        if($stmt->execute()){

            // SEND MAIL ONLY FOR NEW GOOGLE USER
            $body = "
                <h2>Welcome to FileShield 🚀</h2>
                <p>Hi $first,</p>
                <p>Your Google account has been successfully registered.</p>
                <p>You can now securely use FileShield.</p>
            ";

            sendMail($email, "Welcome to FileShield", $body);
        }

        // Get user id
        $getUser = $conn->prepare("SELECT id FROM users WHERE email=?");
        $getUser->bind_param("s",$email);
        $getUser->execute();
        $userId = $getUser->get_result()->fetch_assoc()['id'];

    } else {

        $row = $result->fetch_assoc();
        $userId = $row['id'];
        $provider = $row['provider'];

        if($provider === "local"){
            header("Location: /FileSheild/frontend/google-error.html?error=use_password");
            exit;
        }

        // Existing Google user
        $updateGoogle = $conn->prepare("
            UPDATE users
            SET google_id=?, is_verified=1
            WHERE id=?
        ");

        $updateGoogle->bind_param("ss",$google_id,$userId);
        $updateGoogle->execute();
    }

    //  Generate token
    $auth_token = bin2hex(random_bytes(32));

    $update = $conn->prepare("UPDATE users SET auth_token=? WHERE id=?");
    $update->bind_param("ss",$auth_token,$userId);
    $update->execute();

    //  Redirect
    header("Location: /FileSheild/frontend/google-success.html?token=".$auth_token
        ."&first=".$first
        ."&last=".$last
        ."&email=".$email
        ."&verified=1");
    exit;
}
?>