<?php

$conn = new mysqli("localhost", "root", "", "FileSheild");

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';

if(empty($token) || empty($password)){
    die("Invalid request");
}

// hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// update password + clear token
$stmt = $conn->prepare("
    UPDATE users 
    SET password=?, reset_token=NULL, reset_expiry=NULL 
    WHERE reset_token=?
");
$stmt->bind_param("ss", $hashedPassword, $token);
$stmt->execute();

if($stmt->affected_rows > 0){
    header("Location: /FileSheild/frontend/success.html?message=" . urlencode("Password Updated Sucessfully"));
    
} else {
    header("Location: /FileSheild/frontend/error.html?message=" . urlencode("Token Expired or Invalid"));

}