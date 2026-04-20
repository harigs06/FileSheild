<?php

require 'mailer.php';

function redirectError($msg){
    header("Location: /FileSheild/frontend/error.html?message=" . urlencode($msg));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"] ?? "";
    $first_name = $_POST["first_name"] ?? "";
    $last_name = $_POST["second_name"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!$email || !$first_name || !$last_name || !$password) {
        redirectError("All fields are required");
    }

    $conn = new mysqli("localhost", "root", "", "FileSheild");

    if ($conn->connect_error) {
        redirectError("Database connection failed");
    }

    // Check existing user
    $checkStmt = $conn->prepare("SELECT provider FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row["provider"] === "google") {
            redirectError("Use Google login");
        } else {
            redirectError("Email already exists");
        }
    }

    // Create user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (id, first_name, last_name, email, password, provider)
        VALUES (UUID(), ?, ?, ?, ?, 'local')
    ");

    $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashedPassword);

    if ($stmt->execute()) {

        //  SEND EMAIL AFTER SUCCESS
        $body = "
            <h2>Welcome to FileShield </h2>
            <p>Hi $first_name,</p>
            <p>Your account has been created successfully.</p>
            <p>You can now login and start using the app.</p>
        ";

        sendMail($email, "Welcome to FileShield", $body);

        header("Location: /FileSheild/home.html?registered=true");
        exit();

    } else {
        redirectError("Something went wrong");
    }

    $stmt->close();
    $conn->close();
}