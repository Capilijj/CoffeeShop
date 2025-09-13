<?php
session_start();
require_once '../LoginPage/database_connection.php';
// Only allow access if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
// Handle form submission to update price
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $menuid = $_POST['menuid'];
    $new_price = $_POST['new_price'];
    $stmt = $conn->prepare("UPDATE menu SET price = ? WHERE menuid = ?");
    $stmt->bind_param("di", $new_price, $menuid);
    $stmt->execute();
    $stmt->close();
}
// Handle form submission to add new menu item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu_item'])) {
    $menuname = $_POST['menuname'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Handle file upload
    $imageurl = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = '../Image/';
        $fileName = basename($_FILES['image']['name']);
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
            $imageurl = '../Image/' . $fileName;
        }
    }

    $stmt = $conn->prepare("INSERT INTO menu (menuname, description, price, imageurl) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $menuname, $description, $price, $imageurl);
    $stmt->execute();
    $stmt->close();
}

// Fetch menu items to display
$menu_items = [];
$sql = "SELECT menuid, menuname, description, price, imageurl FROM menu ORDER BY menuid ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
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
        <form method="POST" enctype="multipart/form-data" class="add-menu-form">
            <div>
                <label>Menu Name</label>
                <input type="text" name="menuname" placeholder="Menu Name" required>
            </div>
            <div>
                <label>Description</label>
                <input type="text" name="description" placeholder="Description" required>
            </div>
            <div>
                <label>Price</label>
                <input type="number" name="price" placeholder="Price" min="0" step="0.01" required>
            </div>
            <div>
                <label>Image</label>
                <input type="file" name="image" required>
            </div>
            <button type="submit" name="add_menu_item" style="padding:10px 20px;background:#388e3c;color:#fff;border:none;border-radius:5px;cursor:pointer;font-weight:600;">Add Item</button>
        </form>
        <div class="orders-table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Menu Name</th>
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
                    <td>₱<?= number_format($item['price'], 2) ?></td>
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