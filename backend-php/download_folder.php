<?php

require "filesconfig.php";

$id=$_GET["id"];
$token=$_GET["token"];

/* verify user */

$stmt=$conn->prepare("SELECT id FROM users WHERE auth_token=?");
$stmt->bind_param("s",$token);
$stmt->execute();

$res=$stmt->get_result();

if($res->num_rows===0){
die("Unauthorized");
}

$user=$res->fetch_assoc();
$user_id=$user["id"];


/* get files */

$stmt=$conn->prepare("
SELECT display_name,storage_path
FROM files
WHERE folder_id=? AND user_id=?
");

$stmt->bind_param("is",$id,$user_id);
$stmt->execute();

$res=$stmt->get_result();


$zip=new ZipArchive();
$tmp="folder_".$id.".zip";

$zip->open($tmp,ZipArchive::CREATE);


while($file=$res->fetch_assoc()){

$path=__DIR__."/../storage/".$file["storage_path"];

if(file_exists($path)){
$zip->addFile($path,$file["display_name"]);
}

}

$zip->close();


header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=folder_$id.zip");

readfile($tmp);
unlink($tmp);