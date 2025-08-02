<?php
session_start();

// Check kung naka-login ang user.
if (!isset($_SESSION['user_id'])) {
    // Kung hindi, i-redirect pabalik sa Login page.
    header("Location: ../LoginPage/Login.php");
    exit();
}

$welcome_message = "Welcome to your Dashboard!";
// Kunin ang email sa session para sa personalized welcome message.
if (isset($_SESSION['user_email'])) {
    $welcome_message = "Welcome back, " . htmlspecialchars($_SESSION['user_email']) . "!";
    // Alisin ang email sa session para hindi na ulit lumabas ang welcome message sa susunod na refresh.
    unset($_SESSION['user_email']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <link rel="stylesheet" href="../LoginPage/core.css">
    <link rel="stylesheet" href="../LoginPage/dialogs.css">
    
    <style>
        body {
            background-color: #f8f5f1;
            font-family: 'Open Sans', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            justify-content: center;
        }
        .dashboard-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            width: 90%;
            max-width: 600px;
        }
        .welcome-message {
            font-size: 36px;
            color: #333;
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
            background-color: #dc3545;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
        }
        .logout-btn:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1 class="welcome-message"><?php echo $welcome_message; ?></h1>
        <p class="dashboard-info">You are now logged in. This is your personal dashboard where you can manage your account and view your activities.</p>
        
        <a href="/index.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>