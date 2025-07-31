<?php
// Database connection parameters
$host = "localhost:4306"; // <--- IBINALIK KO ITO DITO (Changed from 'localhost' to 'localhost:3306')
$db_name = "sample_users"; // <--- IBINALIK KO ITO DITO (Changed from 'coffeeshop_db' to 'Sample_Users')
$username = "root";
$password = "";

// Create a new database connection using MySQLi
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>