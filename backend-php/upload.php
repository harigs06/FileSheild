<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

header("Content-Type: application/json");

require "filesconfig.php";

/* ================= AUTH ================= */

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if(!$authHeader){
    echo json_encode(["error"=>"Token missing"]);
    exit;
}

$token = str_replace("Bearer ","",$authHeader);

/* ================= VERIFY USER ================= */

$stmt = $conn->prepare("SELECT id FROM users WHERE auth_token=?");
$stmt->bind_param("s",$token);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(["error"=>"Invalid token"]);
    exit;
}

$user = $result->fetch_assoc();
$user_id = $user['id']; 

/* ================= STORAGE ================= */

$storageDir = __DIR__."/../storage/";

if(!is_dir($storageDir)){
    mkdir($storageDir,0777,true);
}

/* ================= HELPER ================= */

function generateToken(){
    return bin2hex(random_bytes(16));
}

function getOrCreateFolder($conn,$user_id,$folder_name,$parent_id){

    /* CHECK EXISTING */
    $stmt = $conn->prepare("
        SELECT id FROM folders
        WHERE user_id=? AND folder_name=? AND parent_id <=> ?
    ");

    // user_id = string
    $stmt->bind_param("ssi",$user_id,$folder_name,$parent_id);
    $stmt->execute();

    $res = $stmt->get_result();

    if($row = $res->fetch_assoc()){
        return (int)$row['id'];
    }

    /* CREATE NEW FOLDER */
    $folderToken = generateToken();

    $stmt = $conn->prepare("
        INSERT INTO folders(user_id,folder_name,parent_id,share_token)
        VALUES(?,?,?,?)
    ");

    // s = string, s = string, i = int, s = string
    $stmt->bind_param("ssis",$user_id,$folder_name,$parent_id,$folderToken);
    $stmt->execute();

    return $stmt->insert_id;
}

/* ================= VALIDATION ================= */

if(!isset($_FILES['files'])){
    echo json_encode(["error"=>"No files uploaded"]);
    exit;
}

/* ================= UPLOAD ================= */

$uploaded = 0;

foreach($_FILES['files']['tmp_name'] as $i=>$tmpName){

    if($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK){
        continue;
    }

    $displayName = $_FILES['files']['name'][$i];
    $fileSize = (int)$_FILES['files']['size'][$i];
    $mimeType = mime_content_type($tmpName);

    $relativePath = $_POST['paths'][$i] ?? "";
    $folder_id = null;

    /* ================= FOLDER HANDLING ================= */

    if(!empty($relativePath) && strpos($relativePath,"/") !== false){

        $folderPath = dirname($relativePath);
        $folders = explode("/",$folderPath);

        $parent = null;

        foreach($folders as $folder){

            if($folder=="" || $folder==".") continue;

            $parent = getOrCreateFolder($conn,$user_id,$folder,$parent);
        }

        $folder_id = $parent;
    }

    /* ================= FILE STORAGE ================= */

    $random = generateToken();
    $ext = pathinfo($displayName,PATHINFO_EXTENSION);
    $storageName = $random . ($ext ? ".".$ext : "");

    $destination = $storageDir.$storageName;

    if(!move_uploaded_file($tmpName,$destination)){
        continue;
    }

    /* ================= INSERT FILE ================= */

    $shareToken = generateToken();

    $stmt = $conn->prepare("
        INSERT INTO files
        (user_id,folder_id,display_name,storage_path,file_size,mime_type,share_token)
        VALUES(?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "sississ",
        $user_id,       // string
        $folder_id,     // int or null
        $displayName,   // string
        $storageName,   // string
        $fileSize,      // int
        $mimeType,      // string
        $shareToken     // string
    );

    if($stmt->execute()){
        $uploaded++;
    }
}

/* ================= RESPONSE ================= */

echo json_encode([
    "success"=>true,
    "uploaded"=>$uploaded
]);