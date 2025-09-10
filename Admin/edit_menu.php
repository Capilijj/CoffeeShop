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
    <title>Edit Menu</title>
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<?php include 'header.php'; ?>
<div class="admin-container">
    <div class="dashboard-title">Edit Menu</div>
    <div class="admin-section">
        <form method="POST" enctype="multipart/form-data" style="margin-bottom:32px;display:flex;gap:18px;align-items:flex-end;flex-wrap:wrap;justify-content:center;">
            <div>
                <label style="font-weight:600;color:#A0522D;">Image</label><br>
                <input type="file" name="image" accept="image/*" required style="margin-bottom:6px;">
            </div>
            <div>
                <label style="font-weight:600;color:#A0522D;">Name</label><br>
                <input type="text" name="name" required style="padding:6px 10px;border-radius:6px;border:1px solid #ccc;">
            </div>
            <div>
                <label style="font-weight:600;color:#A0522D;">Description</label><br>
                <input type="text" name="description" required style="padding:6px 10px;border-radius:6px;border:1px solid #ccc;min-width:220px;">
            </div>
            <div>
                <label style="font-weight:600;color:#A0522D;">Price</label><br>
                <input type="number" name="price" min="1" step="0.01" required style="padding:6px 10px;border-radius:6px;border:1px solid #ccc;width:90px;">
            </div>
            <div>
                <button type="submit" name="add_menu" style="background:#A0522D;color:#fff;padding:8px 22px;border:none;border-radius:6px;font-weight:700;font-size:1.08rem;">Add</button>
            </div>
        </form>

        <?php
        // Handle add menu
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu'])) {
            $name = $_POST['name'];
            $desc = $_POST['description'];
            $price = $_POST['price'];
            $img_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $img_path = '../ImageMenu/' . uniqid('menu_', true) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $img_path);
                // Keep the full path including '../'
            }
            if ($img_path) {
                $stmt = $conn->prepare('INSERT INTO menu (menuname, description, price, imageurl) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssds', $name, $desc, $price, $img_path);
                $stmt->execute();
                $stmt->close();
                echo '<div style="color:#388e3c;text-align:center;margin-bottom:18px;">Menu item added!</div>';
            } else {
                echo '<div style="color:#a00;text-align:center;margin-bottom:18px;">Image upload failed.</div>';
            }
        }

        // Handle price update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
            $menuid = intval($_POST['menuid']);
            $new_price = floatval($_POST['new_price']);
            if ($menuid > 0 && $new_price > 0) {
                $stmt = $conn->prepare('UPDATE menu SET price = ? WHERE menuid = ?');
                $stmt->bind_param('di', $new_price, $menuid);
                if ($stmt->execute()) {
                    echo '<div style="color:#388e3c;text-align:center;margin-bottom:18px;">Price updated!</div>';
                } else {
                    echo '<div style="color:#a00;text-align:center;margin-bottom:18px;">Failed to update price.</div>';
                }
                $stmt->close();
            }
        }

        // Fetch all menu items
        $menu_items = [];
        $result = $conn->query('SELECT menuid, menuname, description, price, imageurl FROM menu ORDER BY menuid DESC');
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $menu_items[] = $row;
            }
        }
        ?>
        <div style="overflow-x:auto;">
        <table class="orders-table" style="min-width:900px;">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($menu_items)): ?>
                <tr><td colspan="5" style="text-align:center;color:#A0522D;">No menu items found.</td></tr>
            <?php else: foreach ($menu_items as $item): ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($item['imageurl']) ?>" alt="img" style="width:60px;height:60px;object-fit:cover;border-radius:8px;"></td>
                    <td><?= htmlspecialchars($item['menuname']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td>
                        <form method="post" style="display:inline-flex;gap:6px;align-items:center;">
                            <input type="hidden" name="menuid" value="<?= $item['menuid'] ?>">
                            <input type="number" name="new_price" value="<?= htmlspecialchars($item['price']) ?>" min="1" step="0.01" style="width:80px;padding:4px 6px;border-radius:5px;border:1px solid #bbb;">
                            <button type="submit" name="update_price" style="background:#388e3c;color:#fff;padding:4px 14px;border:none;border-radius:5px;font-weight:600;cursor:pointer;">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</body>
</html>