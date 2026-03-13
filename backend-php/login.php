<?php
header("Content-Type: application/json");

$email = $_POST['email'];
$password = $_POST['password'];

$conn = new mysqli("localhost", "root", "", "FileSheild");

$stmt = $conn->prepare("SELECT id, first_name, password, is_verified , email , last_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {

    $stmt->bind_result($userId, $firstName, $hashedPassword, $isVerified , $email , $secondName);
    $stmt->fetch();

    if (password_verify($password, $hashedPassword)) {

        // Generate secure token
        $token = bin2hex(random_bytes(32));

        // Store token
        $update = $conn->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
        $update->bind_param("ss", $token, $userId);
        $update->execute();

        echo json_encode([
            "status" => "success",
            "token" => $token,
            "user_id" => $userId,
            "first_name" => $firstName,
            "is_verified" => (bool)$isVerified,
            "email" => $email,
            "second_name" =>$secondName,
        ]);

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid credentials"
        ]);
    }

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid credentials"
    ]);
}

$stmt->close();
$conn->close();


?>