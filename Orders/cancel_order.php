<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../LoginPage/Login.php');
    exit();
}
require_once '../LoginPage/database_connection.php';
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderid'])) {
    $orderid = intval($_POST['orderid']);
    // Dynamically find the user foreign key column in the orders table
    $user_fk_col = null;
    $result_cols = $conn->query("SHOW COLUMNS FROM orders");
    if ($result_cols) {
        while ($row = $result_cols->fetch_assoc()) {
            if (stripos($row['Field'], 'user') !== false && stripos($row['Field'], 'id') !== false) {
                $user_fk_col = $row['Field'];
                break;
            }
        }
    }
    if ($user_fk_col) {
        $sql = "UPDATE orders SET status = 'Cancelled' WHERE orderid = ? AND `$user_fk_col` = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ii', $orderid, $user_id);
            if (!$stmt->execute()) {
                error_log("Failed to update order status: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare statement: " . $conn->error);
        }
    } else {
        error_log("Could not find user foreign key column in orders table.");
    }
    header('Location: orders.php');
    exit();
}
?>