<?php
session_start();
require_once '../LoginPage/database_connection.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderid'], $_POST['status'])) {
    $orderid = intval($_POST['orderid']);
    $status = $_POST['status'];
    $stmt = $conn->prepare('UPDATE orders SET status=? WHERE orderid=?');
    $stmt->bind_param('si', $status, $orderid);
    $stmt->execute();
    $stmt->close();
}
header('Location: admin.php');
exit();
