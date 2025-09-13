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
    <?php include 'header.php'; ?>
    <div class="admin-container">
        <div class="admin-title">Welcome, Admin!</div>
        <div class="admin-section">
            <p>This is a protected admin area. Only users with the admin role can access this page.</p>
        </div>
        <div class="admin-section">
            <div class="dashboard-title">User Order Dashboard</div>
            <div class="orders-table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Menu Order</th>
                        <th>Total Price</th>
                        <th>Payment Type</th>
                        <th>Proof of Payment</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT o.orderid, u.email, u.address, u.contact_no, o.product_name, o.total_price, o.payment_type, o.proof_of_payment, o.order_date, o.status FROM orders o JOIN users u ON o.userid = u.id ORDER BY o.order_date DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        // Normalize status class for multi-word statuses
                        $status_class = strtolower(str_replace(' ', '-', $row['status']));
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= htmlspecialchars($row['contact_no']) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td>₱<?= number_format($row['total_price'], 2) ?></td>
                        <td><?= htmlspecialchars($row['payment_type']) ?></td>
                        <td>
                            <?php if (!empty($row['proof_of_payment'])): ?>
                                <a href="/<?= htmlspecialchars($row['proof_of_payment']) ?>" target="_blank">View Proof</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['order_date']))) ?></td>
                        <td class="order-status <?= $status_class ?>"><?= htmlspecialchars(ucwords($row['status'])) ?></td>
                        <td>
                            <form method="post" action="update_order_status.php" style="display:flex;gap:6px;align-items:center;">
                                <input type="hidden" name="orderid" value="<?= $row['orderid'] ?>">
                                <select name="status" style="padding:4px 8px;border-radius:5px;">
                                    <option value="Pending" <?= $row['status']==='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Processing" <?= $row['status']==='Processing'?'selected':'' ?>>Processing</option>
                                    <option value="Payment Verified" <?= $row['status']==='Payment Verified'?'selected':'' ?>>Payment Verified</option>
                                    <option value="In Progress" <?= $row['status']==='In Progress'?'selected':'' ?>>In Progress</option>
                                    <option value="Completed" <?= $row['status']==='Completed'?'selected':'' ?>>Completed</option>
                                    <option value="Invalid" <?= $row['status']==='Invalid'?'selected':'' ?>>Invalid</option>
                                </select>
                                <button type="submit" style="background:#A0522D;color:#fff;border:none;padding:6px 14px;border-radius:5px;font-weight:600;cursor:pointer;">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="10" style="text-align:center;color:#A0522D;">No orders found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</body>
</html>