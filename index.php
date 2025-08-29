<?php
// Magsimula ng session
session_start();
include_once 'LoginPage/database_connection.php';

$message_to_display = "";
$redirect_after_dialog = "";
$open_otp_modal = false;
$otp_verification_message = "";
$otp_verification_status = "";
$is_password_reset_flow = false;

// Check kung kailangan nating buksan ang OTP modal
if (isset($_SESSION['open_otp_modal_on_load']) && $_SESSION['open_otp_modal_on_load']) {
    $open_otp_modal = true;
    unset($_SESSION['open_otp_modal_on_load']);

    $is_password_reset_flow = $_SESSION['is_password_reset_flow'] ?? false;

    if (isset($_SESSION['otp_verification_message'])) {
        $otp_verification_message = $_SESSION['otp_verification_message'];
        $otp_verification_status = $_SESSION['otp_verification_status'] ?? '';
        unset($_SESSION['otp_verification_message']);
        unset($_SESSION['otp_verification_status']);
    }
}

// --- PHP Login Form Submission Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    if (isset($_POST["email"]) && isset($_POST["password"])) {
        $email_input = trim($_POST["email"]);
        $password_input = $_POST["password"];

        // Use role column from Users table (ENUM)
        $stmt = $conn->prepare("SELECT id, email, password, profile_img, role FROM Users WHERE email = ?");
        if ($stmt === false) {
            $message_to_display = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $email_input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password_input, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_role'] = isset($user['role']) ? strtolower($user['role']) : 'user';
                    // Set profile image session for header
                    if (!empty($user['profile_img'])) {
                        $_SESSION['user_profile_img'] = $user['profile_img'];
                    } else {
                        unset($_SESSION['user_profile_img']);
                    }
                    $message_to_display = "Login successful! Welcome, " . htmlspecialchars($user['email']) . ".";
                    // Redirect based on role
                    if ($_SESSION['user_role'] === 'admin') {
                        $redirect_after_dialog = "/Admin/admin.php";
                    } else {
                        $redirect_after_dialog = "/Dashboard/dashboard.php";
                    }
                } else {
                    $message_to_display = "Incorrect password. Please try again.";
                }
            } else {
                $message_to_display = "This email is not registered. Please create an account.";
            }
            $stmt->close();
        }
    } else {
        $message_to_display = "Please enter both email and password.";
    }
}

// --- PHP Signup Form Submission Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup_submit'])) {
    if (isset($_POST["signup_email"]) && isset($_POST["signup_password"]) && isset($_POST["confirm_password"]) && isset($_POST["signup_name"])) {
        $email_input = trim($_POST["signup_email"]);
        $password_input = $_POST["signup_password"];
        $confirm_password_input = $_POST["confirm_password"];
        $name_input = trim($_POST["signup_name"]);

        if (empty($email_input) || empty($password_input) || empty($confirm_password_input) || empty($name_input)) {
            $message_to_display = "Please fill in all fields for registration.";
        } else if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
            $message_to_display = "Invalid email format.";
        } else if (!str_ends_with(strtolower($email_input), '@gmail.com')) {
            $message_to_display = "Only Gmail addresses are allowed.";
        } else if ($password_input !== $confirm_password_input) {
            $message_to_display = "Passwords do not match.";
        } else if (strlen($password_input) < 8) {
            $message_to_display = "Password must be at least 8 characters.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM Users WHERE email = ?");
            if ($stmt === false) {
                $message_to_display = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param("s", $email_input);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $message_to_display = "Email already registered. Try logging in.";
                } else {
                    $_SESSION['pending_signup_password'] = $password_input;
                    $_SESSION['pending_signup_name'] = $name_input;
                    $_SESSION['otp_email_for_verification'] = $email_input;
                    $_SESSION['open_otp_modal_on_load'] = true;
                    $_SESSION['is_password_reset_flow'] = false;

                    // TAMANG PATH para sa redirect
                    header("Location: LoginPage/send_otp_email.php");
                    exit();
                }
                $stmt->close();
            }
        }
    } else {
        $message_to_display = "Please fill out the signup form completely.";
    }
}

// --- PHP Forgot Password Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_password_submit'])) {
    if (isset($_POST["forgot_email"])) {
        $forgot_email = trim($_POST["forgot_email"]);

        if (!filter_var($forgot_email, FILTER_VALIDATE_EMAIL)) {
    $message_to_display = "Please enter a valid email address.";
} else {
    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id FROM Users WHERE email = ?");
    if ($stmt === false) {
        $message_to_display = "Database error: " . $conn->error;
    } else {
        $stmt->bind_param("s", $forgot_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email exists - proceed with OTP flow
            $_SESSION['otp_email_for_verification'] = $forgot_email;
            $_SESSION['open_otp_modal_on_load'] = true;
            $_SESSION['is_password_reset_flow'] = true;

            header("Location: LoginPage/send_otp_email.php");
            exit();
        } else {
            // Email not registered
            $message_to_display = "No account found with that email. Please check and try again.";
        }

        $stmt->close();
    }
}

    } else {
        $message_to_display = "Please enter your email for password reset.";
    }
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Home - CoffeeCraft</title>
    
    <link rel="stylesheet" href="LoginPage/core.css">
    <link rel="stylesheet" href="LoginPage/dialogs.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <?php
    include_once 'header.php';
    ?>

    <main>
        <?php
        // Dito natin i-i-include ang Login.php
        include 'LoginPage/Login.php';
        ?>
    </main>

    <?php
    include_once 'footer.php';
    ?>

    <script src="LoginPage/Login.js" defer></script>
    <script src="../responsive.js"></script>
</body>
</html>
