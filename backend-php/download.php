<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

require "filesconfig.php";


/* GET PARAMETERS */

$id = $_GET["id"] ?? null;

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

/* also allow token via query for browser redirect */

if(!$authHeader && isset($_GET["token"])){
    $authHeader = "Bearer ".$_GET["token"];
}

if(!$authHeader){
    die("Token missing");
}

$token = str_replace("Bearer ","",$authHeader);


/* VERIFY USER */

$stmt = $conn->prepare("SELECT id FROM users WHERE auth_token=?");
$stmt->bind_param("s",$token);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Invalid token");
}

$user = $result->fetch_assoc();
$user_id = $user["id"];


/* GET FILE METADATA */

$stmt = $conn->prepare("
SELECT display_name, storage_path, mime_type
FROM files
WHERE id=? AND user_id=?
");

$stmt->bind_param("is",$id,$user_id);
$stmt->execute();

$res = $stmt->get_result();

if($res->num_rows === 0){
    die("File not found");
}

$file = $res->fetch_assoc();


/* BUILD STORAGE PATH */

$storageDir = __DIR__."/../storage/";
$path = $storageDir . basename($file["storage_path"]);

if(!file_exists($path)){
    die("File missing on server");
}


/* SEND FILE */

$name = $file["display_name"];
$type = $file["mime_type"];
$size = filesize($path);

header("Content-Type: ".$type);
header("Content-Disposition: attachment; filename=\"".$name."\"");
header("Content-Length: ".$size);
header("Cache-Control: no-cache");

readfile($path);
exit;