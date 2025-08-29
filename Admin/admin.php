<?php
session_start();
require_once '../LoginPage/database_connection.php';

// Only allow access if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <div class="admin-header">
        Admin Dashboard
        <a href="../LoginPage/logout.php" style="float:right; color:#A0522D; font-size:16px; text-decoration:none; margin-right:20px;">Logout <i class="fa fa-sign-out-alt"></i></a>
    </div>
    <div class="admin-container">
        <div class="admin-title">Welcome, Admin!</div>
        <div class="admin-section">
            <p>This is a protected admin area. Only users with the admin role can access this page.</p>
        </div>
        <div class="admin-section">
            <h3>Quick Actions</h3>
            <ul>
                <li><a href="#" style="color:#A0522D;">Manage Users</a></li>
                <li><a href="#" style="color:#A0522D;">View Reports</a></li>
                <li><a href="#" style="color:#A0522D;">Site Settings</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
