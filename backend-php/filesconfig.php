<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "FileSheild";

$conn = new mysqli($host,$user,$pass,$db);

if($conn->connect_error){
    die("DB Connection failed");
}