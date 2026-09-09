<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "mediquick_pharmacy";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (!defined('BASE_PATH')) {
    // includes/db.php -> dirname(__DIR__) = project root
    // (the folder that directly contains both "includes/" and "public/")
    define('BASE_PATH', dirname(__DIR__));
}
 
if (!defined('UPLOAD_BASE_PATH')) {
    // Adjust this if your web-accessible folder isn't "public/"
    define('UPLOAD_BASE_PATH', BASE_PATH . '/public/uploads/');
    // TEMPORARY DEBUG — remove after checking
    // die("db.php is at: " . __FILE__ . "<br>BASE_PATH = " . BASE_PATH . "<br>UPLOAD_BASE_PATH = " . UPLOAD_BASE_PATH);
}