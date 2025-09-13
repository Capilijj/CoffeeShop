<?php

session_start();
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
    
    // Find the user foreign key column (usually user_id or users_id or similar)
    $user_fk_col = null;
    foreach ($columns as $col) {
        if (stripos($col, 'user') !== false && stripos($col, 'id') !== false) {
            $user_fk_col = $col;
            break;
        }
    }
    
    // Find a valid column for ORDER BY
    if (in_array('order_date', $columns)) {
        $order_by_col = 'order_date';
    } elseif (in_array('created_at', $columns)) {
        $order_by_col = 'created_at';
    } else {
        $order_by_col = isset($columns[0]) ? $columns[0] : '';
    }

    if ($user_fk_col && $order_by_col) {
        // Only select needed columns for display
        $select_cols = array_intersect($needed, $columns);
        $col_sql = implode(',', array_map(function($col){return "`$col`";}, $select_cols));
        $sql = "SELECT $col_sql FROM orders WHERE `$user_fk_col` = ? ORDER BY `$order_by_col` DESC";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result_data = $stmt->get_result();
            if ($result_data) {
                while ($order_row = $result_data->fetch_assoc()) {
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
        $debug_message = 'Could not find user foreign key column or order by column in orders table.';
    }
} else if ($result === false) {
    $debug_message = 'SHOW TABLES query failed: ' . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="orders.css">
    <link rel="stylesheet" href="../LoginPage/core.css">
    <link rel="stylesheet" href="../header.css">
    <link rel="stylesheet" href="../footer.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <main>
    <div class="orders-container">
    <div class="orders-title">My Orders</div>
    <?php if (!empty($debug_message)) echo '<div style="color:red;">'.$debug_message.'</div>'; ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Orders #</th>
                    <th>Menu</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" style="text-align:center;color:#A0522D;">No orders found.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                <?php
                // Correct order of columns for display
                $display_cols = ['orderid','product_name','quantity','total_price','payment_type','status','order_date'];
                foreach ($display_cols as $col) {
                    $val = isset($order[$col]) && $order[$col] !== null ? (string)$order[$col] : '';
                    if ($col === 'total_price') {
                        echo '<td>₱'.htmlspecialchars($val).'</td>';
                    } else if ($col === 'status') {
                        echo '<td class="order-status '.strtolower($val).'">'.htmlspecialchars(ucfirst($val)).'</td>';
                    } else if ($col === 'order_date') {
                        // Format the date to be more readable
                        if (!empty($val)) {
                            $formatted_date = date('Y-m-d H:i:s', strtotime($val));
                            echo '<td>'.htmlspecialchars($formatted_date).'</td>';
                        } else {
                            echo '<td></td>';
                        }
                    } else {
                        echo '<td>'.htmlspecialchars($val).'</td>';
                    }
                }
                ?>
                    <td>
                        <?php
                        // Show Cancel button if order is less than 5 minutes old AND the status is not already cancelled
                        $is_cancellable = false;
                        $is_cancelled = false;
                        if (isset($order['order_date']) && isset($order['status'])) {
                            $order_time = strtotime($order['order_date']);
                            $now = time();
                            $is_cancelled = (strtolower($order['status']) == 'cancelled');
                            // Check if the order is recent (within 5 minutes) and not already cancelled
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
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    </main>
    <?php include '../footer.php'; ?>

    <div id="cancel-modal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <p>Are you sure you want to cancel this order?</p>
            <div class="modal-buttons">
                <button id="modal-yes-btn">Yes</button>
                <button id="modal-no-btn">No</button>
            </div>
        </div>
    </div>
    
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
    });
    </script>
</body>
</html>