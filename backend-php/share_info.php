<?php

header("Content-Type: application/json");

require "filesconfig.php";

$token = $_GET["token"] ?? null;

if(!$token){
echo json_encode(["error"=>"Invalid token"]);
exit;
}

$stmt = $conn->prepare("
SELECT display_name,file_size,mime_type
FROM files
WHERE share_token=?
");

$stmt->bind_param("s",$token);
$stmt->execute();

$res = $stmt->get_result();

if($res->num_rows === 0){
echo json_encode(["error"=>"File not available"]);
exit;
}

$file = $res->fetch_assoc();

echo json_encode($file);