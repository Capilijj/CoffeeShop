<?php
// Include the database connection file at the very beginning
include_once 'database_connection.php'; // Make sure this file is in the same directory

// Start a session for user management (e.g., storing login status)
session_start();

$message_to_display = ""; // Initialize a variable to hold the message
$redirect_after_dialog = ""; // Initialize variable for JS redirect

// Variables for OTP modal state
$open_otp_modal = false;
$otp_verification_message = "";
$otp_verification_status = "";
$is_password_reset_flow = false; // Initialize to false

// Check if we need to immediately open the OTP modal
if (isset($_SESSION['open_otp_modal_on_load']) && $_SESSION['open_otp_modal_on_load']) {
    $open_otp_modal = true;
    unset($_SESSION['open_otp_modal_on_load']); // Consume the flag

    $is_password_reset_flow = $_SESSION['is_password_reset_flow'] ?? false; // Get the flow type from session

    // Check for messages from OTP verification (if set by verify_otp.php after a redirect)
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
        $email_input = trim($_POST["email"]); // Trim whitespace
        $password_input = $_POST["password"];

        // Prepare the SQL statement to select the user by email
        $stmt = $conn->prepare("SELECT id, email, password FROM Users WHERE email = ?");

        // Check if the prepare statement failed
        if ($stmt === false) {
            $message_to_display = "Database error (prepare failed): " . $conn->error;
        } else {
            // Bind the parameter (email_input) to the statement
            $stmt->bind_param("s", $email_input); // "s" means the parameter is a string

            // Execute the statement
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            // Check if a user with that email exists
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                // Verify the password
                if (password_verify($password_input, $user['password'])) {
                    // Password is correct
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['loggedin'] = true;

                    // Set message and flag for JavaScript to handle redirect after dialog
                    $message_to_display = "Login successful! Welcome, " . htmlspecialchars($user['email']) . ".";
                    $redirect_after_dialog = "/Dashboard/dashboard.php"; // Target URL for redirect

                    // IMPORTANT: Do NOT redirect here immediately. Let JavaScript handle it after dialog.
                    // exit(); // Don't exit here, we need to render the HTML with the message flag.
                } else {
                    // Incorrect password
                    $message_to_display = "Incorrect password. Please try again.";
                }
            } else {
                // No user found with that email
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
    if (isset($_POST["signup_email"]) && isset($_POST["signup_password"]) && isset($_POST["confirm_password"])) {
        $email_input = trim($_POST["signup_email"]);
        $password_input = $_POST["signup_password"];
        $confirm_password_input = $_POST["confirm_password"];

        // Validation
        if (empty($email_input) || empty($password_input) || empty($confirm_password_input)) {
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
            // Check if user already exists
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
                    // ✅ Store PLAIN password for hashing in verify_otp.php after successful OTP
                    $_SESSION['pending_signup_password'] = $password_input;
                    $_SESSION['otp_email_for_verification'] = $email_input;
                    $_SESSION['open_otp_modal_on_load'] = true;
                    $_SESSION['is_password_reset_flow'] = false; // Indicate this is for signup

                    // Redirect to a specific URL that handles sending the OTP email
                    // For simplicity, we can redirect to a dedicated script, or
                    // handle the OTP sending here and then redirect to Login.php
                    // to display the OTP modal.
                    // For now, let's assume `send_otp_email.php` will be called.
                    header("Location: send_otp_email.php"); // Redirect to send OTP email
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
            // Store email in session and trigger OTP process for password reset
            $_SESSION['otp_email_for_verification'] = $forgot_email;
            $_SESSION['open_otp_modal_on_load'] = true; // Flag to open OTP modal on next load
            $_SESSION['is_password_reset_flow'] = true; // Flag for OTP to know it's for reset

            // Redirect to a specific URL that handles sending the OTP email
            header("Location: send_otp_email.php"); // Redirect to send OTP email
            exit();
        }
    } else {
        $message_to_display = "Please enter your email for password reset.";
    }
}


// Close the database connection at the very end of the script
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - CoffeeCraft</title>
    <link rel="stylesheet" href="core.css">
    <link rel="stylesheet" href="dialogs.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <header class="header">
        <img src="../image/Logo.png" alt="CoffeeCraft Logo" class="header-logo" />
        <button class="nav-toggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="nav-links" id="navbarNav">
            <a href="#">Home</a>
            <a href="#">Menu</a>
            <a href="#">About Us</a>
            <a href="#">Services</a>
            <a href="#">Contact Us</a>
            <a href="#" class="nav-login-btn">Log In</a>
        </nav>
    </header>

    <main>
        <div class="split-wrapper">
            <div class="left-panel">
                <h1>Welcome to CoffeeCraft</h1>
                <p>Start your day right with the perfect blend of flavor and comfort. Experience the rich aroma and exquisite taste of our finest brews.</p>
            </div>

            <div class="right-panel">
                <div class="container">
                    <div class="tab-header" id="mainTabHeader">
                        <button class="tab active" data-tab="login">Login</button>
                        <button class="tab" data-tab="signup">Create Account</button>
                    </div>

                    <div class="login-box form-content active" id="login">
                        <h2>Welcome to CoffeeCraft!</h2>
                        <form action="Login.php" method="POST">
                            <div class="input-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="email" />
                            </div>

                            <div class="input-group">
                                <label for="password">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password" />
                                    <i class="toggle-password fa fa-eye-slash"></i>
                                </div>
                            </div>

                            <div class="forgot-wrapper">
                                <a href="#" class="forgot-password-link" id="switchToForgot">Forgot Password?</a>
                            </div>

                            <button type="submit" name="login_submit" class="login-btn">Login</button>

                            <div class="create-account-bottom">
                                <span>Don't have an account? <a href="#" id="switchToSignup">Create Account</a></span>
                            </div>
                        </form>
                    </div>

                    <div class="login-box form-content" id="signup">
                        <h2>Create Account</h2>
                        <form action="Login.php" method="POST" id="signupForm">
                            <div class="input-group">
                                <label for="signup-email">Email</label>
                                <input type="email" id="signup-email" name="signup_email" placeholder="Enter your email" required autocomplete="email" />
                            </div>

                            <div class="input-group">
                                <label for="signup-password">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="signup-password" name="signup_password" placeholder="Create a password" required autocomplete="new-password" />
                                    <i class="toggle-password fa fa-eye-slash"></i>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="confirm-password">Confirm Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password" />
                                    <i class="toggle-password fa fa-eye-slash"></i>
                                </div>
                            </div>

                            <button type="submit" name="signup_submit" class="login-btn">Sign Up</button>

                            <div class="create-account-bottom">
                                <span>Already have an account? <a href="#" id="switchToLogin">Login</a></span>
                            </div>
                        </form>
                    </div>

                    <div class="login-box form-content" id="forgotPassword">
                        <h2>Forgot Your Password?</h2>
                        <p class="forgot-password-intro">Enter your email address below and we'll send you an OTP to reset your password.</p>
                        <form action="Login.php" method="POST">
                            <div class="input-group">
                                <label for="forgot-email">Email</label>
                                <input type="email" id="forgot-email" name="forgot_email" placeholder="Enter your email" required autocomplete="email" />
                            </div>
                            <button type="submit" name="forgot_password_submit" class="login-btn">Send OTP</button>
                            <div class="back-to-login">
                                <a href="#" id="backToLoginFromForgot">Back to Login</a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 CoffeeCraft. All rights reserved.</p>
    </footer>

    <?php if (!empty($message_to_display)): ?>
        <input type="hidden" id="phpMessage" value="<?php echo htmlspecialchars($message_to_display); ?>">
        <input type="hidden" id="phpRedirectUrl" value="<?php echo htmlspecialchars($redirect_after_dialog); ?>">
    <?php endif; ?>

    <div id="validationDialog" class="dialog-container">
        <div class="dialog-content" id="dialogContent">
            <span class="close-button" id="dialogCloseButton">&times;</span>
            <p id="dialogMessage"></p>
            <button class="dialog-ok-button" id="dialogOkButton">OK</button>

            <div id="otpDialogContent" style="display: none;">
                <h2>OTP Verification</h2>
                <div class="message-box" id="otpMessageBox">
                    </div>
                <p id="otpInstruction">Please enter the 6-digit verification code sent to <br><strong id="otpEmailDisplay"></strong></p>

                <form id="otpVerificationForm" method="post" action="verify_otp.php"> <div class="otp-input-group-boxes">
                        <input type="text" class="otp-box" id="otpBox1" data-index="0" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="otp-box" id="otpBox2" data-index="1" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="otp-box" id="otpBox3" data-index="2" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="otp-box" id="otpBox4" data-index="3" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="otp-box" id="otpBox5" data-index="4" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="otp-box" id="otpBox6" data-index="5" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                    </div>
                    <div class="timer" id="otpTimer"></div>
                    <div class="otp-actions">
                        <button type="submit" class="dialog-ok-button" id="verifyOtpButton">Verify OTP</button>
                    </div>
                </form>
                <form id="resendOtpForm" method="post" action="send_otp_email.php" style="margin-top: 10px;"> <button type="submit" class="dialog-ok-button resend-link" id="resendOtpButton">Resend OTP</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Pass PHP variables to JavaScript
        const phpOpenOtpModal = <?php echo json_encode($open_otp_modal); ?>;
        const phpOtpEmailForVerification = <?php echo json_encode($_SESSION['otp_email_for_verification'] ?? ''); ?>;
        const phpOtpVerificationMessage = <?php echo json_encode($otp_verification_message); ?>;
        const phpOtpVerificationStatus = <?php echo json_encode($otp_verification_status); ?>;
        const phpIsPasswordResetFlow = <?php echo json_encode($is_password_reset_flow); ?>;

        // PHP message for general dialog (e.g., login success, or other errors)
        const phpMessage = document.getElementById('phpMessage')?.value || '';
        const phpRedirectUrl = document.getElementById('phpRedirectUrl')?.value || '';

        // Clear these session variables in PHP if they've been passed (this is handled in PHP directly now)
        // <?php // unset($_SESSION['otp_email_for_verification']); ?>
        // <?php // unset($_SESSION['is_password_reset_flow']); ?>
        // The PHP variables are read directly into JS constants, no need to unset here.
        // Unsetting after reading by JS might lead to issues if PHP needs them for subsequent logic on the same page load.
    </script>
    <script src="Login.js" defer></script>
</body>
</html>