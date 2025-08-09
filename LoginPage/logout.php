<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_profile_img'])) {
    unset($_SESSION['user_profile_img']);
}
session_start();
session_unset();
session_destroy();
header('Location: /index.php');
exit();
