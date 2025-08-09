</header>
<script src="/profile-dropdown.js"></script>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userProfileImg = isset($_SESSION['user_profile_img']) ? $_SESSION['user_profile_img'] : null;
?>
<header class="header">
    <img src="../image/Logo.png" alt="CoffeeCraft Logo" class="header-logo" />
    <button class="nav-toggle" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
    </button>
    <nav class="nav-links" id="navbarNav">
        <a href="/Dashboard/dashboard.php">Home</a>
        <a href="/Dashboard/dashboard.php#products">Menu</a>
        <a href="/Dashboard/dashboard.php#about-us">About Us</a>
        <a href="/Dashboard/dashboard.php#services">Services</a>
        <a href="/Dashboard/dashboard.php#contact-us">Contact Us</a>
        <?php if ($isLoggedIn): ?>
            <a href="/Profile/profile.php" class="nav-profile-text-link">Profile</a>
        <?php endif; ?>
        <?php if ($isLoggedIn): ?>
            <div class="nav-profile-btn-wrapper" style="margin-left:auto;">
                <a href="#" class="nav-profile-btn" id="profileBtn">
                    <span class="profile-circle">
                        <?php
                        // Robust fix: Always use correct relative path for uploaded images
                        $imgSrc = $userProfileImg;
                        // Remove any leading / or ./
                        if ($imgSrc) {
                            $imgSrc = ltrim($imgSrc, '/');
                            $imgSrc = preg_replace('/^\.\//', '', $imgSrc);
                            if (stripos($imgSrc, '../') !== 0) {
                                $imgSrc = '../' . $imgSrc;
                            }
                            // Check if file exists (relative to this file)
                            $imgPathForCheck = __DIR__ . '/' . ltrim(str_replace('../', '', $imgSrc), '/');
                            if (!file_exists($imgPathForCheck) || stripos($imgSrc, 'logo.png') !== false) {
                                $imgSrc = '../Image/Logo.png';
                            }
                        } else {
                            $imgSrc = '../Image/Logo.png';
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Profile" />
                    </span>
                </a>
                <div class="profile-dropdown" id="profileDropdown" style="display:none;">
                    <form action="/LoginPage/logout.php" method="POST">
                        <button type="submit" class="logout-btn enhanced">Log Out <i class="fas fa-sign-out-alt"></i></button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <a href="/index.php" class="nav-login-btn">Log In</a>
        <?php endif; ?>
    </nav>
</header>