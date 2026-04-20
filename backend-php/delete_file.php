<?php

header("Content-Type: application/json");

require "filesconfig.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data["id"] ?? null;
$token = $data["token"] ?? null;

if(!$id || !$token){
echo json_encode(["error"=>"Invalid request"]);
exit;
}


/* verify user */

$stmt = $conn->prepare("SELECT id FROM users WHERE auth_token=?");
$stmt->bind_param("s",$token);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
echo json_encode(["error"=>"Unauthorized"]);
exit;
}

$user = $res->fetch_assoc();
$user_id = $user["id"];


/* get file info */

$stmt = $conn->prepare("
SELECT storage_path FROM files
WHERE id=? AND user_id=?
");

$stmt->bind_param("is",$id,$user_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
echo json_encode(["error"=>"File not found"]);
exit;
}

$file = $res->fetch_assoc();


/* delete physical file */

$path = __DIR__."/../storage/".$file["storage_path"];

if(file_exists($path)){
unlink($path);
}


/* delete DB record */

$stmt = $conn->prepare("DELETE FROM files WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();


echo json_encode(["success"=>true]);