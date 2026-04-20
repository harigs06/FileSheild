<?php

header("Content-Type: application/json");
require "filesconfig.php";

$token = $_GET['token'] ?? '';

if (!$token) {
    echo json_encode(["error" => "Invalid token"]);
    exit;
}

/* ================= GET FOLDER ================= */

$stmt = $conn->prepare("
SELECT id, folder_name
FROM folders
WHERE share_token=?
");

$stmt->bind_param("s", $token);
$stmt->execute();

$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["error" => "Folder not found"]);
    exit;
}

$folder = $res->fetch_assoc();
$folder_id = $folder['id'];

/* ================= FILES ================= */

$stmt = $conn->prepare("
SELECT display_name, file_size, share_token
FROM files
WHERE folder_id=?
");

$stmt->bind_param("i", $folder_id);
$stmt->execute();

$files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ================= SUBFOLDERS ================= */

$stmt = $conn->prepare("
SELECT folder_name, share_token
FROM folders
WHERE parent_id=?
");

$stmt->bind_param("i", $folder_id);
$stmt->execute();

$folders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ================= RESPONSE ================= */

echo json_encode([
    "folder" => $folder,
    "files" => $files,
    "folders" => $folders
]);