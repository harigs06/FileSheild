<?php
header("Content-Type: application/json");

$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$token = $headers['Authorization'];

$conn = new mysqli("localhost", "root", "", "user_management");

$stmt = $conn->prepare("SELECT id FROM users WHERE auth_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    echo json_encode(["error" => "Invalid token"]);
    exit();
}

echo json_encode(["status" => "Access granted"]);
?>