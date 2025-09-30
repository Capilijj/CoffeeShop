<?php
// FILE: admin_addons.php

session_start();
require_once '../LoginPage/database_connection.php';
// Only allow access if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// ------------------------------------------------
// 1. ADD NEW ADD-ON
// ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_addon'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $extra_price = floatval($_POST['extra_price']);
    $is_available = 1; // Default to available

    if (!empty($name) && !empty($category)) {
        $stmt = $conn->prepare("INSERT INTO addon_options (name, category, extra_price, is_available) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $name, $category, $extra_price, $is_available);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_addons.php');
    exit();
}

// ------------------------------------------------
// 2. DELETE ADD-ON
// ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_addon'])) {
    $addon_id = intval($_POST['addon_id']);
    $stmt = $conn->prepare("DELETE FROM addon_options WHERE id = ?");
    $stmt->bind_param("i", $addon_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_addons.php');
    exit();
}

// ------------------------------------------------
// 3. TOGGLE STOCK STATUS (is_available)
// ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_stock'])) {
    $addon_id = intval($_POST['addon_id']);
    $current_status = intval($_POST['current_status']);
    // I-reverse ang status (1 -> 0 or 0 -> 1)
    $new_status = $current_status == 1 ? 0 : 1; 

    $stmt = $conn->prepare("UPDATE addon_options SET is_available = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $addon_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_addons.php');
    exit();
}

// ------------------------------------------------
// 4. FETCH ALL ADD-ONS
// ------------------------------------------------
$addons = [];
$result = $conn->query("SELECT * FROM addon_options ORDER BY category, name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $addons[] = $row;
    }
}

// Group by category for display
$grouped_addons = [];
foreach ($addons as $addon) {
    $grouped_addons[$addon['category']][] = $addon;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Add-ons</title>
    <link rel="stylesheet" href="css/admin.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        /* Basic styling kung walang admin.css */
        .admin-container { padding-top: 80px; padding-bottom: 40px; max-width: 1200px; margin: auto; }
        .admin-title { text-align: center; color: #5c4033; margin-bottom: 25px; font-size: 2rem; }
        .admin-section { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .add-form, .addons-list { width: 100%; }
        .add-form input[type="text"], .add-form input[type="number"], .add-form select { 
            padding: 8px; margin-right: 10px; border: 1px solid #ccc; border-radius: 5px; 
            width: 180px; 
        }
        .add-form button { 
            background: #388e3c; color: #fff; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; 
        }
        .addon-category h3 { color: #A0522D; border-bottom: 2px solid #f0e6e0; padding-bottom: 5px; margin-top: 20px; }
        .addon-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed #eee; }
        .addon-details { flex-grow: 1; }
        .addon-details span { font-weight: 600; color: #5c4033; }
        .addon-price { color: #388e3c; font-weight: 500; margin-left: 10px; }
        .addon-actions button { margin-left: 8px; padding: 5px 10px; border-radius: 5px; border: none; cursor: pointer; color: #fff; font-weight: 600; }
        .delete-btn { background-color: #f44336; }
        .stock-btn-in { background-color: #2196f3; }
        .stock-btn-out { background-color: #ff9800; }
        .out-of-stock { color: #f44336; font-weight: 700; }
        .in-stock { color: #388e3c; font-weight: 700; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="admin-container">
        <div class="admin-title">Manage Coffee Add-on Options</div>

        <div class="admin-section">
            <h3>Add New Add-on</h3>
            <form method="POST" class="add-form">
                <input type="text" name="name" placeholder="Add-on Name (e.g., Oat Milk)" required>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Size">Size</option>
                    <option value="Temperature">Temperature</option>
                    <option value="Sugar Level">Sugar Level</option>
                    <option value="Milk">Milk</option>
                    <option value="Shot">Shot</option>
                    <option value="Syrup">Syrup</option>
                    <option value="Topping">Topping</option>
                    <option value="Other">Other</option>
                </select>
                <input type="number" name="extra_price" placeholder="Extra Price (e.g., 20.00)" step="0.01" min="0" required>
                <button type="submit" name="add_addon">Add Add-on</button>
            </form>
        </div>

        <div class="admin-section addons-list">
            <h3>Current Add-ons Status</h3>
            
            <?php if (empty($addons)): ?>
                <p style="text-align: center; color: #A0522D;">No add-ons found.</p>
            <?php else: ?>
                <?php foreach ($grouped_addons as $category => $list): ?>
                    <div class="addon-category">
                        <h3><?= htmlspecialchars($category) ?> (<?= count($list) ?>)</h3>
                        <?php foreach ($list as $addon): ?>
                            <div class="addon-item">
                                <div class="addon-details">
                                    <span><?= htmlspecialchars($addon['name']) ?></span>
                                    <span class="addon-price">(+₱<?= number_format($addon['extra_price'], 2) ?>)</span>
                                    <span style="margin-left: 15px;">
                                        Status: 
                                        <?php if ($addon['is_available'] == 1): ?>
                                            <span class="in-stock">IN STOCK</span>
                                        <?php else: ?>
                                            <span class="out-of-stock">NO STOCK</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="addon-actions">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="addon_id" value="<?= $addon['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $addon['is_available'] ?>">
                                        <button type="submit" name="toggle_stock" class="stock-btn-<?= $addon['is_available'] == 1 ? 'out' : 'in' ?>">
                                            <?= $addon['is_available'] == 1 ? 'Mark No Stock' : 'Mark In Stock' ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this Add-on?');">
                                        <input type="hidden" name="addon_id" value="<?= $addon['id'] ?>">
                                        <button type="submit" name="delete_addon" class="delete-btn">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>