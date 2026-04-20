<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendMail($toEmail, $subject, $body) {

    $config = require __DIR__ . '/config.php';

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
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}