<?php

header("Content-Type: application/json");

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require 'config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);

if($conn->connect_error){
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

$email = $_POST['email'] ?? '';

if(empty($email)){
    echo json_encode(["status" => "error", "message" => "Email missing"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, provider FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(["status" => "error", "message" => "No user found"]);
    exit;
}

$user = $result->fetch_assoc();

if($user['provider'] === 'google'){
    echo json_encode([
        "status" => "error",
        "message" => "Use Google login"
    ]);
    exit;
}

$token = bin2hex(random_bytes(32));
$expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

$stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expiry=? WHERE email=?");
$stmt->bind_param("sss", $token, $expiry, $email);
$stmt->execute();

$resetLink = "http://localhost/FileSheild/backend-php/reset-password.php?token=$token";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = $config['smtp_email'];
    $mail->Password = $config['app_password'];

    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom($config['smtp_email'], 'FileShield');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body = "
        <h3>Password Reset</h3>
        <p>Click below to reset your password:</p>
        <a href='$resetLink'>$resetLink</a>
        <p>This link expires in 1 hour.</p>
    ";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "Reset link sent to email"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Mailer Error: " . $mail->ErrorInfo
    ]);
}