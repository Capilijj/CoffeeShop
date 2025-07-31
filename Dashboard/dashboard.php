<?php
session_start(); // Simulan ang session sa simula ng page

// Check kung naka-login ang user
if (!isset($_SESSION['user_id'])) {
    // Kung hindi naka-login, i-redirect sa login page
    header("Location: Login.php");
    exit();
}

$welcome_message = "";
// Kunin ang email mula sa session kung available
if (isset($_SESSION['user_email'])) {
    $welcome_message = "Welcome back!, " . htmlspecialchars($_SESSION['user_email']) . "!";
    // Alisin ang email sa session para hindi na ulit lumabas ang welcome message sa susunod na refresh
    unset($_SESSION['user_email']);
}

// Optionally, you can fetch more user details from the database using $_SESSION['user_id'] here
// Example (assuming $conn is available or you include database_connection.php again):
/*
include_once 'database_connection.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM Users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$username = $user_data['username'] ?? 'User'; // Use $username in your welcome message if preferred
$stmt->close();
*/

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CoffeeCraft</title>
    <link rel="stylesheet" href="style.css"> <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            flex-direction: column;
        }
        .dashboard-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        .welcome-message {
            font-size: 28px;
            color: #00704A; /* Green accent */
            margin-bottom: 20px;
            font-weight: bold;
        }
        .dashboard-info {
            font-size: 16px;
            color: #555;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .logout-btn {
            background-color: #dc3545; /* Red for logout */
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            text-decoration: none; /* Remove underline for link */
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block; /* Allows padding and centering */
        }
        .logout-btn:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php if (!empty($welcome_message)): ?>
            <h1 class="welcome-message"><?php echo $welcome_message; ?></h1>
        <?php else: ?>
            <h1 class="welcome-message">Welcome to your Dashboard!</h1>
        <?php endif; ?>

        <p class="dashboard-info">You are now logged in. This is your personalized dashboard where you can manage your account.</p>
        <p class="dashboard-info">Explore our features or proceed with your activities.</p>

        <a href="/LoginPage/Login.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>