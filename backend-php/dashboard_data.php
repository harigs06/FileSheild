<?php

header("Content-Type: application/json");
require "filesconfig.php";

$data = json_decode(file_get_contents("php://input"), true);
$token = $data["token"] ?? "";

$folder = $_GET['folder'] ?? null;

if(!$token){
echo json_encode(["error"=>"Unauthorized"]);
exit;
}

/* USER */

$stmt=$conn->prepare("SELECT id FROM users WHERE auth_token=?");
$stmt->bind_param("s",$token);
$stmt->execute();

$res=$stmt->get_result();

if($res->num_rows===0){
echo json_encode(["error"=>"Invalid token"]);
exit;
}

$user=$res->fetch_assoc();
$user_id=$user["id"];


/* FILES */

if($folder){
$stmt=$conn->prepare("
SELECT id,display_name,file_size,share_token
FROM files
WHERE user_id=? AND folder_id=?
");
$stmt->bind_param("si",$user_id,$folder);
}else{
$stmt=$conn->prepare("
SELECT id,display_name,file_size,share_token
FROM files
WHERE user_id=? AND folder_id IS NULL
");
$stmt->bind_param("s",$user_id);
}

$stmt->execute();
$files=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);


/* FOLDERS */

if($folder){
$stmt=$conn->prepare("
SELECT id,folder_name
FROM folders
WHERE user_id=? AND parent_id=?
");
$stmt->bind_param("si",$user_id,$folder);
}else{
$stmt=$conn->prepare("
SELECT id,folder_name,share_token
FROM folders
WHERE user_id=? AND parent_id IS NULL
");
$stmt->bind_param("s",$user_id);
}

$stmt->execute();
$folders=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);


/* BREADCRUMB */

function getPath($conn,$folder_id){

$path=[];

while($folder_id){

$stmt=$conn->prepare("SELECT id,folder_name,parent_id FROM folders WHERE id=?");
$stmt->bind_param("i",$folder_id);
$stmt->execute();

$res=$stmt->get_result();

if($row=$res->fetch_assoc()){
array_unshift($path,[
"id"=>$row["id"],
"name"=>$row["folder_name"]
]);
$folder_id=$row["parent_id"];
}else{
break;
}

}

return $path;
}

$path = $folder ? getPath($conn,$folder) : [];

echo json_encode([
"files"=>$files,
"folders"=>$folders,
"path"=>$path
]);