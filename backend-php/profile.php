<?php

header("Content-Type: application/json");

// DB connection
$conn = new mysqli("localhost", "root", "", "FileSheild");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Get token from header
$headers = getallheaders();
$auth_token = $headers['Authorization'] ?? '';

if (!$auth_token) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Remove "Bearer " if present
$auth_token = str_replace("Bearer ", "", $auth_token);

// Get user
$stmt = $conn->prepare("SELECT id, first_name, last_name, email FROM users WHERE auth_token = ?");
$stmt->bind_param("s", $auth_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
    exit;
}

$user = $result->fetch_assoc();
$user_id = $user['id'];

// -------------------- GET PROFILE --------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Get storage used
    $stmt2 = $conn->prepare("SELECT COALESCE(SUM(file_size),0) as total FROM files WHERE user_id = ?");
    $stmt2->bind_param("s", $user_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $storage = $res2->fetch_assoc();

    echo json_encode([
        "first_name" => $user['first_name'],
        "last_name" => $user['last_name'],
        "email" => $user['email'],
        "used_storage" => (int)$storage['total'],
        "max_storage" => 1073741824 // 1GB
    ]);
    exit;
}

// -------------------- UPDATE PROFILE --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    $first_name = $data['first_name'] ?? null;
    $last_name = $data['last_name'] ?? null;
    $password = $data['password'] ?? null;

    // Build dynamic query
    $fields = [];
    $params = [];
    $types = "";

    if ($first_name) {
        $fields[] = "first_name = ?";
        $params[] = $first_name;
        $types .= "s";
    }

    if ($last_name) {
        $fields[] = "last_name = ?";
        $params[] = $last_name;
        $types .= "s";
    }

    if ($password) {
        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(["error" => "Password must be at least 6 characters"]);
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $fields[] = "password = ?";
        $params[] = $hashed;
        $types .= "s";
    }

    if (empty($fields)) {
        echo json_encode(["message" => "Nothing to update"]);
        exit;
    }

    $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $params[] = $user_id;
    $types .= "s";

    $stmt3 = $conn->prepare($query);
    $stmt3->bind_param($types, ...$params);

    if ($stmt3->execute()) {
        echo json_encode(["message" => "Profile updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Update failed"]);
    }

    exit;
}

?>