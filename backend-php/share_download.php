<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

require "filesconfig.php";

$token = $_GET["token"] ?? null;
$preview = isset($_GET["preview"]);

if(!$token){
die("Invalid link");
}

/* fetch file */

$stmt = $conn->prepare("
SELECT display_name,storage_path,mime_type
FROM files
WHERE share_token=?
");

$stmt->bind_param("s",$token);
$stmt->execute();

$res = $stmt->get_result();

if($res->num_rows === 0){
die("File not available");
}

$file = $res->fetch_assoc();

/* path */

$path = __DIR__."/../storage/".basename($file["storage_path"]);

if(!file_exists($path)){
die("File missing");
}

/* headers */

$name = $file["display_name"];
$type = $file["mime_type"];
$size = filesize($path);

header("Content-Type: ".$type);

if($preview){
header("Content-Disposition: inline; filename=\"$name\"");
}else{
header("Content-Disposition: attachment; filename=\"$name\"");
}

header("Content-Length: ".$size);

/* output */

readfile($path);
exit;