<?php
// FILE: orders.php

// FIX: Gumamit ng conditional session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../LoginPage/Login.php');
    exit();
}
require_once '../LoginPage/database_connection.php';
$user_id = $_SESSION['user_id'];
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

$orders = [];
$debug_message = '';
// Check if orders table exists and fetch orders for this user
$result = $conn->query("SHOW TABLES LIKE 'orders'");
if ($result && $result->num_rows > 0) {
    // Dynamically get columns
    $result_cols = $conn->query("SHOW COLUMNS FROM orders");
    $columns = [];
    if ($result_cols) {
        while ($row = $result_cols->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }

    $needed = ['orderid','product_name','quantity','total_price','payment_type','status','order_date'];
    
    // Find the user foreign key column (usually userid or users_id or similar)
    $user_fk_col = null;
    foreach ($columns as $col) {
        if (stripos($col, 'user') !== false && stripos($col, 'id') !== false) {
            $user_fk_col = $col;
            break;
        }
    }
    
    // Find the order by column (order_date or orderid)
    $order_by_col = in_array('order_date', $columns) ? 'order_date' : (in_array('orderid', $columns) ? 'orderid' : null);

    if ($user_fk_col && $order_by_col) {
        $select_cols = array_intersect($needed, $columns);
        $col_sql = implode(',', array_map(function($col){return "`$col`";}, $select_cols));
        
        // Use prepared statement for security
        $sql = "SELECT $col_sql FROM orders WHERE `$user_fk_col` = ? ORDER BY `$order_by_col` DESC";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result_data = $stmt->get_result();
            
            if ($result_data) {
                while ($order_row = $result_data->fetch_assoc()) {
                    $order_id = $order_row['orderid'];
                    
                    // CRITICAL LOGIC: Fetch Add-ons for this specific order
                    $addons_sql = "SELECT ao.name, ao.extra_price, ao.category
                                   FROM order_addons oa 
                                   JOIN addon_options ao ON oa.addon_id = ao.id 
                                   WHERE oa.orderid = ?
                                   ORDER BY 
                                        CASE ao.category 
                                            WHEN 'Size' THEN 1 
                                            WHEN 'Temperature' THEN 2 
                                            WHEN 'Sugar Level' THEN 3
                                            WHEN 'Milk' THEN 4
                                            WHEN 'Shot' THEN 5
                                            WHEN 'Syrup' THEN 6
                                            WHEN 'Topping' THEN 7
                                            ELSE 8 /* Para sa iba pang Addons */
                                        END, ao.name ASC";

                    $addons_stmt = $conn->prepare($addons_sql);
                    $addons_stmt->bind_param('i', $order_id);
                    $addons_stmt->execute();
                    $addons_result = $addons_stmt->get_result();
                    $order_addons = [];
                    while ($addon = $addons_result->fetch_assoc()) {
                        $order_addons[] = $addon;
                    }
                    $addons_stmt->close();
                    
                    $order_row['addons'] = $order_addons; // I-attach ang addons sa order row
                    $orders[] = $order_row;
                }
            } else {
                $debug_message = 'Failed to get result set: ' . $conn->error;
            }
            $stmt->close();
        } else {
            $debug_message = 'SQL Prepare failed: ' . $conn->error;
        }
    } else {
        $debug_message = 'Could not find required columns in orders table.';
    }
} else {
    $debug_message = 'Orders table does not exist.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="../LoginPage/core.css">
    <link rel="stylesheet" href="orders.css">
    <link rel="stylesheet" href="../header.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="orders-container">
        <h2 class="orders-title">My Orders</h2>
        
        <?php if (!empty($debug_message)): ?>
            <p style="color: red; text-align: center;"><?= htmlspecialchars($debug_message) ?></p>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p>You have no orders yet.</p>
        <?php else: ?>
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th style="min-width: 200px;">Menu Item</th>
                            <th>Qty</th>
                            <th>Total Price</th>
                            <th>Payment Type</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['orderid'] ?? '') ?></td>
                                
                                <td class="order-menu-details">
                                    <div style="font-weight: 700; color: #5c4033; margin-bottom: 5px;"><?= htmlspecialchars($order['product_name'] ?? '') ?></div>
                                    <?php if (!empty($order['addons'])): ?>
                                        <ul style="list-style-type: none; padding: 0; margin-top: 0; font-size: 0.9em; margin-bottom: 0;">
                                            <?php 
                                            // Group add-ons by category to display nicely
                                            $grouped_addons = [];
                                            foreach ($order['addons'] as $addon) {
                                                $grouped_addons[$addon['category']][] = $addon;
                                            }
                                            foreach ($grouped_addons as $category => $addons):
                                            ?>
                                                <li style="color: #666; font-weight: 500; margin-top: 3px;">
                                                    <span style="color: #A0522D; font-weight: 700;"><?= htmlspecialchars($category) ?>:</span>
                                                    <?php 
                                                        echo implode(', ', array_map(function($addon) {
                                                            $price_str = $addon['extra_price'] > 0 ? ' (+' . number_format($addon['extra_price'], 2) . ')' : '';
                                                            return htmlspecialchars($addon['name']) . ($price_str ? '<span style="color: #388e3c;">' . $price_str . '</span>' : '');
                                                        }, $addons));
                                                    ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                
                                <td><?= htmlspecialchars($order['quantity'] ?? '') ?></td>
                                <td style="font-weight: 600;">₱<?= number_format($order['total_price'] ?? 0.00, 2) ?></td>
                                <td><?= htmlspecialchars($order['payment_type'] ?? '') ?></td>
                                <td class="order-status <?= strtolower($order['status'] ?? 'pending') ?>"><?= htmlspecialchars(ucfirst($order['status'] ?? '')) ?></td>
                                <td>
                                    <?php 
                                        if (!empty($order['order_date'])) {
                                            // Format date and time
                                            echo htmlspecialchars(date('M d, Y H:i', strtotime($order['order_date'])));
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $is_cancellable = false;
                                    $order_date_val = $order['order_date'] ?? null;
                                    $order_status_val = $order['status'] ?? null;

                                    if ($order_date_val && $order_status_val) {
                                        $order_time = strtotime($order_date_val);
                                        $now = time();
                                        $is_cancelled = (strtolower($order_status_val) == 'cancelled');
                                        // Can cancel within 5 minutes (300 seconds) AND status is not Cancelled
                                        if (($now - $order_time < 300) && !$is_cancelled) { 
                                            $is_cancellable = true;
                                        }
                                    }
                                    ?>
                                    <form method="post" action="cancel_order.php" class="cancel-form">
                                        <input type="hidden" name="orderid" value="<?= htmlspecialchars($order['orderid']) ?>">
                                        <button type="submit" class="cancel-btn"<?= $is_cancellable ? '' : ' disabled' ?>>Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
    </div>

    <div id="cancel-modal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <p>Are you sure you want to cancel this order?</p>
            <div class="modal-buttons">
                <button id="modal-yes-btn">Yes</button>
                <button id="modal-no-btn">No</button>
            </div>
        </div>
    </div>
        <?php include '../footer.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('cancel-modal');
        const modalYesBtn = document.getElementById('modal-yes-btn');
        const modalNoBtn = document.getElementById('modal-no-btn');
        let formToSubmit = null;

        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                if (this.disabled) return; // Don't show modal if button is disabled
                event.preventDefault();
                formToSubmit = this.closest('form');
                modal.style.display = 'flex';
            });
        });

        modalYesBtn.addEventListener('click', function() {
            if (formToSubmit) {
                formToSubmit.submit();
            }
            modal.style.display = 'none';
        });

        modalNoBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            formToSubmit = null;
        });

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                formToSubmit = null;
            }
        });
    });
    </script>
</body>
</html>