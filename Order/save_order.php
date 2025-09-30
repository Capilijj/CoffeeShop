<?php
// FIX: Gumamit ng conditional session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Assumes your database connection file is in '../LoginPage/database_connection.php'
require_once '../LoginPage/database_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Check if user is logged in
    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "User not logged in."]);
        exit();
    }

    // 1. Get ALL POST data (including calculated total_price and addons)
    $menuid = intval($_POST['menuid'] ?? 0);
    $product_name = trim($_POST['menu'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    
    // Tiyakin na ang total_price ay galing sa client-side calculation (kasama ang add-ons)
    $total_price = floatval($_POST['total_price'] ?? 0.00); 

    $payment_type = trim($_POST['payment_type'] ?? 'GCash');
    $proof_path = null;
    $errors = [];

    // Ang $_POST['addons'] ay maglalaman ng array ng IDs mula sa checkboxes at radio buttons
    $all_addons = $_POST['addons'] ?? []; 
    $addon_ids = [];
    foreach ($all_addons as $key => $value) {
        // Tinitiyak na ang value ay integer (Addon ID)
        // Ang check para sa 'none' ay nasa JS, pero magandang may dagdag na check dito
        if (is_numeric($value)) { 
            $addon_ids[] = intval($value);
        }
    }


    // 2. Handle Proof of Payment (File Upload)
    if ($payment_type === 'GCash' && isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
        $target_dir = '../uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $filename = 'proof_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES['proof']['tmp_name'], $target_file)) {
            $proof_path = $target_file;
        } else {
            $errors[] = "Failed to upload proof of payment.";
        }
    } elseif ($payment_type === 'GCash' && (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK)) {
        // I-check kung required ang proof. Sa JS, ginawa itong required kung GCash.
        // Hahayaan nating ang JS validation ang mag-handle nito, pero ito ay safety check.
        // $errors[] = "Proof of payment is required for GCash."; 
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
        exit();
    }
    
    // 3. Main Order INSERT into `orders` table
    $status = 'Pending';
    
    $sql = "INSERT INTO orders (userid, menuid, product_name, quantity, total_price, payment_type, proof_of_payment, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iisdssss", $user_id, $menuid, $product_name, $quantity, $total_price, $payment_type, $proof_path, $status);
        
        if ($stmt->execute()) {
            $new_order_id = $conn->insert_id;

            // 4. I-insert ang Add-ons sa `order_addons` table
            if (!empty($addon_ids)) {
                $addon_placeholders = implode(',', array_fill(0, count($addon_ids), "(?, ?)")); // Removed quantity=1 from placeholders
                $addon_sql = "INSERT INTO order_addons (orderid, addon_id) VALUES " . $addon_placeholders; // Assuming order_addons only needs orderid, addon_id
                $addon_stmt = $conn->prepare($addon_sql);

                // Prepare parameters: repeat 'ii' (orderid, addon_id) for each addon
                $types = str_repeat('ii', count($addon_ids));
                $params = [];
                foreach ($addon_ids as $addon_id) {
                    $params[] = $new_order_id;
                    $params[] = $addon_id;
                }

                // Bind parameters
                if (!empty($params)) {
                     // Gumamit ng call_user_func_array para i-bind ang variable number ng params
                    $bind_names[] = $types;
                    for ($i = 0; $i < count($params); $i++) {
                        $bind_name = 'bind' . $i;
                        $$bind_name = $params[$i];
                        $bind_names[] = &$$bind_name;
                    }
                    call_user_func_array(array($addon_stmt, 'bind_param'), $bind_names);
                    
                    $addon_stmt->execute();
                    $addon_stmt->close();
                }
            }
            
            // 5. I-check ang User's Feedback Status (NEW LOGIC)
            $feedback_status = 0;
            // Ang column na ito ay dapat sa `users` table: `has_given_onetime_feedback`
            $stmt_check = $conn->prepare("SELECT has_given_onetime_feedback FROM users WHERE id = ?"); 
            if ($stmt_check) {
                $stmt_check->bind_param('i', $user_id);
                $stmt_check->execute();
                $stmt_check->bind_result($feedback_status);
                $stmt_check->fetch();
                $stmt_check->close();
            }
            
            // 'show_feedback_prompt' is TRUE if status is 0 (has not given feedback)
            $show_feedback = ($feedback_status == 0);
            

            // Success Response (UPDATED to include the new flag)
            echo json_encode([
                'success' => true, 
                'order_id' => $new_order_id,
                'show_feedback_prompt' => $show_feedback // New flag to control modal display
            ]);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Order INSERT failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'SQL Prepare failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, "message" => "Invalid request method or missing user ID."]);
}
?>