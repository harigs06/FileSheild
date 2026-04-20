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

/* VERIFY USER */

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


/* 🔴 STEP 1: get all files inside folder */

$stmt = $conn->prepare("
SELECT storage_path FROM files
WHERE folder_id=? AND user_id=?
");

$stmt->bind_param("is",$id,$user_id);
$stmt->execute();

$res = $stmt->get_result();

$storageDir = __DIR__."/../storage/";

/* 🔴 STEP 2: delete physical files */

while($file = $res->fetch_assoc()){

$path = $storageDir . $file["storage_path"];

if(file_exists($path)){
unlink($path);
}

}


/* 🔴 STEP 3: delete folder (cascade deletes DB files) */

$stmt = $conn->prepare("
DELETE FROM folders
WHERE id=? AND user_id=?
");

$stmt->bind_param("is",$id,$user_id);
$stmt->execute();


echo json_encode(["success"=>true]);