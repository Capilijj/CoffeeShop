<?php
session_start();
require_once 'database_connection.php';

if (!isset($_SESSION['reset_password_email'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['reset_password_email'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_pass = trim($_POST['new_password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');

    if (empty($new_pass) || empty($confirm_pass)) {
        $message = "Please fill in both password fields.";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "Passwords do not match.";
    } elseif (strlen($new_pass) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Users SET password = ? WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                unset($_SESSION['reset_password_email']);
                header("Location: ../index.php?reset=success");
                exit();
            } else {
                $message = "Error updating password. Please try again.";
            }
            $stmt->close();
        } else {
            $message = "Database error. Please contact support.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="core.css">
    <link rel="stylesheet" href="reset_password.css"> <!-- External CSS -->
</head>
<body>
    <div class="reset-container">
        <h2>Reset Your Password</h2>
        <form method="POST" autocomplete="off">
            <div class="input-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required minlength="8">
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required minlength="8">
            </div>

            <button type="submit">Reset Password</button>
        </form>

        <!-- Error message will be shown as a popup dialog -->
        <p class="back-link-container">
            <a href="../index.php">Back to Login</a>
        </p>
    </div>
</body>
<div id="validationDialog" class="dialog-container">
    <div class="dialog-content" id="dialogContent">
        <span class="close-button" id="dialogCloseButton">&times;</span>
        <p id="dialogMessage"></p>
        <button class="dialog-ok-button" id="dialogOkButton">OK</button>
    </div>
</div>
<script>
    // Show PHP error message as popup dialog if present
    const phpMessage = <?php echo json_encode($message); ?>;
    if (phpMessage) {
        const validationDialog = document.getElementById('validationDialog');
        const dialogMessage = document.getElementById('dialogMessage');
        const dialogOkButton = document.getElementById('dialogOkButton');
        const dialogCloseButton = document.getElementById('dialogCloseButton');
        function showValidationDialog(message) {
            dialogMessage.textContent = message;
            validationDialog.classList.add('show');
        }
        dialogOkButton.addEventListener('click', function() {
            validationDialog.classList.remove('show');
        });
        dialogCloseButton.addEventListener('click', function() {
            validationDialog.classList.remove('show');
        });
        showValidationDialog(phpMessage);
    }
</script>
</html>
