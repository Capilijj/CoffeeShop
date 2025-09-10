<?php
session_start();
require_once '../LoginPage/database_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT address, contact_no FROM users WHERE id = ?');
if (!$stmt) {
    echo json_encode(['error' => 'db_error']);
    exit();
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($address, $contact_no);
$stmt->fetch();
$stmt->close();

// Return as JSON
// If empty, return as empty string
$response = [
    'address' => $address ?? '',
    'contact_no' => $contact_no ?? ''
];
echo json_encode($response);
