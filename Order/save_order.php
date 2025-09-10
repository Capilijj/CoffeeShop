<?php
session_start();
require_once '../LoginPage/database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $menuid = $_POST['menuid'] ?? null;
    $product_name = $_POST['menu'] ?? '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $total_price = $quantity * $price;
    $order_date = date('Y-m-d H:i:s');
    $payment_type = $_POST['payment_type'] ?? 'GCash';
    $proof_path = null;

    // Handle proof of payment upload if present
    if (isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
        $target_dir = '../uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $filename = 'proof_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES['proof']['tmp_name'], $target_file)) {
            $proof_path = 'uploads/' . $filename;
        }
    }

    if ($user_id && $menuid && $product_name && $quantity > 0) {
        $stmt = $conn->prepare("INSERT INTO orders (userid, menuid, product_name, quantity, total_price, payment_type, proof_of_payment, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iisidsss', $user_id, $menuid, $product_name, $quantity, $total_price, $payment_type, $proof_path, $order_date);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Order placed successfully!"]);
        } else {
            // Add debug info for troubleshooting
            $debug = [
                'POST' => $_POST,
                'FILES' => $_FILES,
                'SESSION' => $_SESSION,
                'sql_error' => $stmt->error,
                'user_id' => $user_id,
                'product_name' => $product_name,
                'quantity' => $quantity,
                'price' => $price,
                'total_price' => $total_price,
                'payment_type' => $payment_type,
                'proof_path' => $proof_path,
                'order_date' => $order_date
            ];
            echo json_encode([
                "success" => false,
                "message" => "Order failed. Please try again.",
                "debug" => $debug
            ]);
        }
        $stmt->close();
    } else {
        // Add debug info for troubleshooting
        $debug = [
            'POST' => $_POST,
            'FILES' => $_FILES,
            'SESSION' => $_SESSION,
            'user_id' => $user_id,
            'product_name' => $product_name,
            'quantity' => $quantity,
            'price' => $price,
            'total_price' => $total_price,
            'payment_type' => $payment_type,
            'proof_path' => $proof_path,
            'order_date' => $order_date
        ];
        echo json_encode([
            "success" => false,
            "message" => "Invalid order data.",
            "debug" => $debug
        ]);
    }
    $conn->close();
    exit();
}
?>
