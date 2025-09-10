<?php
session_start();
require_once '../LoginPage/database_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $menuid = $_POST['menuid'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    $payment_type = $_POST['payment_type'] ?? '';
    $proof = $_FILES['proof'] ?? null;
    $errors = [];

    if (!$user_id) $errors[] = 'User not logged in.';
    if (!$menuid) $errors[] = 'Menu ID missing.';
    if (!$payment_type) $errors[] = 'Payment type missing.';

    // Fetch menu details from DB
    $menu = $desc = $price = $imageurl = null;
    if ($menuid) {
        $stmt_menu = $conn->prepare("SELECT menuname, description, price, imageurl FROM menu WHERE menuid = ?");
        if ($stmt_menu) {
            $stmt_menu->bind_param('i', $menuid);
            $stmt_menu->execute();
            $stmt_menu->bind_result($menu, $desc, $price, $imageurl);
            if (!$stmt_menu->fetch()) {
                $errors[] = 'Menu item not found.';
            }
            $stmt_menu->close();
        } else {
            $errors[] = 'Menu lookup failed: ' . $conn->error;
        }
    }

    // Handle file upload (optional, just check for now)
    $proof_path = null;
    if ($proof && $proof['tmp_name']) {
        $target_dir = '../uploads/';
        $filename = 'proof_' . time() . '_' . rand(1000,9999) . '.' . pathinfo($proof['name'], PATHINFO_EXTENSION);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($proof['tmp_name'], $target_file)) {
            $proof_path = $target_file;
        } else {
            $errors[] = 'Failed to upload proof.';
        }
    }

    if (count($errors) > 0) {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
        exit();
    }

    // Insert order into DB
    $stmt = $conn->prepare("INSERT INTO orders (menuid, product_name, quantity, total_price, payment_type, status, order_date, user_id, proof) VALUES (?, ?, ?, ?, ?, 'Pending', NOW(), ?, ?)");
    if ($stmt) {
        $total_price = floatval($price) * intval($quantity);
        $stmt->bind_param('isidiss', $menuid, $menu, $quantity, $total_price, $payment_type, $user_id, $proof_path);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    }
    exit();
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>
