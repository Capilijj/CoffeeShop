<?php
// Database connection parameters
$host = "localhost";
$port = 3306;
$db_name = "sample_users";
$username = "root";
$password = "";

// Create a new database connection using MySQLi
$conn = new mysqli($host, $username, $password, $db_name, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>