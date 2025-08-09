<?php
session_start();
$welcome_message = "Welcome to your Dashboard!";
if (isset($_SESSION['user_email'])) {
    $welcome_message = "Welcome back, " . htmlspecialchars($_SESSION['user_email']) . "!";
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
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="../LoginPage/dialogs.css">
    <link rel="stylesheet" href="../header.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<?php include '../header.php'; ?>

<div class="main-content">
    <div class="content-section">
        <h1 class="main-title">Enjoy The Most Delicious Coffee</h1>
        <p class="subtitle">Start Your Day With Coffee, Enhancing Productivity And Mood. Its Invigorating Aroma Sets A Focused Tone For Tackling Tasks With Renewed Energy And Positivity.</p>
        <div class="button-container">
            <button class="action-button">Explore</button>
            <button class="action-button secondary">Order Coffee</button>
        </div>
    </div>
</div>

<?php include '../sections/menu.php'; ?>

<div class="scroll-sections-container">
    <?php include '../sections/aboutus.php'; ?>
    <?php include '../sections/services.php'; ?>
    <?php include '../sections/contactus.php'; ?>
</div>

<?php include '../footer.php'; ?>

<script src="../responsive.js"></script>
</body>
</html>