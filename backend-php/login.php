<?php
header("Content-Type: application/json");

$email = $_POST['email'];
$password = $_POST['password'];

$conn = new mysqli("localhost", "root", "", "FileSheild");

$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {

    $stmt->bind_result($userId, $hashedPassword);
    $stmt->fetch();

    if (password_verify($password, $hashedPassword)) {

        // Generate secure token
        $token = bin2hex(random_bytes(32));

        // Store token in DB
        $update = $conn->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
        $update->bind_param("ss", $token, $userId);
        $update->execute();

        echo json_encode([
            "status" => "success",
            "token" => $token,
            "user_id" => $userId
        ]);

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
}
?>