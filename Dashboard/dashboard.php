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

<?php 
// Ensure menu.css is loaded for the menu section
echo '<link rel="stylesheet" href="../sections/menu.css">';
echo '<div id="products" class="menu-section">';
include '../sections/menu.php'; 
echo '</div>';
?>

<div class="scroll-sections-container">
    <div id="about-us" class="scroll-section">
        <h2>About Us</h2>
        <div class="section-content-container">
            <p>Welcome to our coffee shop, where passion for quality coffee meets a cozy and inviting atmosphere. We believe in the power of a good cup of coffee to bring people together, spark conversations, and create moments of pure enjoyment.</p>
            <p>Our journey began with a simple mission: to serve exceptional coffee using only the finest, ethically sourced beans. We take pride in our craft, from the careful selection of beans to the perfect roast and precise brewing process. Every cup is a testament to our dedication to flavor and quality.</p>
        </div>
    </div>

    <div id="services" class="scroll-section">
        <h2>Services</h2>
        <div class="service-container">
            <div class="service-card">
                <i class="fas fa-truck-fast"></i>
                <h3>Fast Delivery</h3>
                <p>We deliver your coffee hot and fresh right to your doorstep.</p>
            </div>
            <div class="service-card">
                <i class="fas fa-coffee"></i>
                <h3>Quality Products</h3>
                <p>We use only the finest beans from local farms.</p>
            </div>
            <div class="service-card">
                <i class="fas fa-sack-dollar"></i>
                <h3>Affordable Price</h3>
                <p>Enjoy our delicious coffee without breaking the bank.</p>
            </div>
        </div>
    </div>

    <div id="contact-us" class="scroll-section">
        <h2>Contact Us</h2>
        <div class="section-content-container">
            <p>We would love to hear from you! Whether you have a question about our menu, need to place a large order, or just want to say hello, feel free to reach out.</p>
            <div class="contact-info">
                <div>
                    <i class="fas fa-location-dot"></i>
                    <span>123 Coffee Bean Lane, Brew City, CA 90210</span>
                </div>
                <div>
                    <i class="fas fa-phone"></i>
                    <span>(123) 456-7890</span>
                </div>
                <div>
                    <i class="fas fa-envelope"></i>
                    <span>info@donutcafe.com</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '../footer.php'; 
?>
<script src="dashboard.js"></script>
<script src="../responsive.js"></script>
</body>
</html>