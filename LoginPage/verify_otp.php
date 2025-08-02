<?php
session_start();
include('database_connection.php'); // Siguraduhin na tama ang path na ito

$otp_lifetime = 120; // 5 minuto (300 segundo)

// Simulan ang mga mensahe ng session kung hindi pa nakatakda
if (!isset($_SESSION['otp_verification_message'])) $_SESSION['otp_verification_message'] = '';
if (!isset($_SESSION['otp_verification_status'])) $_SESSION['otp_verification_status'] = '';

// Ipagpalagay na ito ay isang AJAX request bilang default kung ito ay isang POST, maliban kung tahasang hindi
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred.',
    'redirect_url' => null // Para sa tagumpay na redirect
];

// I-validate ang email session
$user_email = $_SESSION['otp_email_for_verification'] ?? null;
if (!$user_email) {
    
    if ($is_ajax_request) {
        echo json_encode($response);
        exit();
    } else {
        $_SESSION['otp_verification_message'] = $response['message'];
        $_SESSION['otp_verification_status'] = $response['status'];
        header("Location: ../index.php");
        exit();
    }
}

// Suriin kung naroroon ang OTP sa session
if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_created_at'])) {
    $response['message'] = "No OTP found. Please request a new one.";
    if ($is_ajax_request) {
        echo json_encode($response);
        exit();
    } else {
        $_SESSION['otp_verification_message'] = $response['message'];
        $_SESSION['otp_verification_status'] = $response['status'];
        header("Location: ../index.php");
        exit();
    }
}

// Suriin kung nag-expire na ang OTP
$time_elapsed = time() - $_SESSION['otp_created_at'];
if ($time_elapsed > $otp_lifetime) {
    $response['message'] = "OTP has expired. Please request a new one.";
    // I-clear ang nag-expire na OTP mula sa session
    unset($_SESSION['otp'], $_SESSION['otp_created_at']);
    if ($is_ajax_request) {
        echo json_encode($response);
        exit();
    } else {
        $_SESSION['otp_verification_message'] = $response['message'];
        $_SESSION['otp_verification_status'] = $response['status'];
        header("Location: ../index.php");
        exit();
    }
}

// --- OTP Verification Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kunin ang pinagsamang OTP mula sa JS (corrected parameter name)
    $user_entered_otp = $_POST['otp'] ?? '';
    // Kunin ang flow type mula sa JS (corrected parameter name)
    $is_password_reset_flow = (isset($_POST['is_password_reset_flow']) && $_POST['is_password_reset_flow'] === 'true');

    // Sanitize at i-validate ang input
    $user_entered_otp = filter_var($user_entered_otp, FILTER_SANITIZE_NUMBER_INT);

    // Suriin kung ang inilagay na OTP ay tumutugma sa nasa session
    if ($user_entered_otp === $_SESSION['otp']) {
        // ✅ Tumugma ang OTP, i-clear ang OTP mula sa session
        unset($_SESSION['otp'], $_SESSION['otp_created_at']);

        // Tukuyin ang daloy (signup o password reset)
        if (!$is_password_reset_flow) { // ✅ ACCOUNT CREATION / VERIFICATION FLOW
            // I-check muna kung may existing user na may email na ito,
            // pero hindi pa verified.
            $stmt_check = $conn->prepare("SELECT id FROM Users WHERE email = ? AND verified = 0");
            if ($stmt_check === false) {
                $response['message'] = "Database error (check prepare failed): " . $conn->error;
            } else {
                $stmt_check->bind_param("s", $user_email);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();

                if ($result_check->num_rows > 0) {
                    // May existing user na hindi pa verified, i-update ang 'verified' column
                    $stmt_update = $conn->prepare("UPDATE Users SET verified = 1 WHERE email = ?");
                    if ($stmt_update === false) {
                        $response['message'] = "Database error (update prepare failed): " . $conn->error;
                    } else {
                        $stmt_update->bind_param("s", $user_email);
                        if ($stmt_update->execute()) {
                            $response['status'] = "success";
                            $response['message'] = "Account successfully verified! You can now log in.";
                            $_SESSION['user_email'] = $user_email;
                            $_SESSION['loggedin'] = true;
                            // I-clear ang signup-specific na data ng session
                            unset($_SESSION['pending_signup_password']); // Clear plain password from session
                            unset($_SESSION['otp_email_for_verification']); // I-clear ang email pagkatapos ng matagumpay na signup/verification
                            unset($_SESSION['is_password_reset_flow']); // I-clear ang flow flag
                            $response['redirect_url'] = "/Dashboard/dashboard.php"; // I-redirect sa dashboard pagkatapos ng matagumpay na verification
                        } else {
                            $response['message'] = "Error verifying account: " . $stmt_update->error;
                        }
                        $stmt_update->close();
                    }
                } else {
                    // Walang existing user na hindi pa verified, o kaya bagong signup.
                    // Ito ang original logic para sa pag-insert ng bagong user.
                    $plain_password = $_SESSION['pending_signup_password'] ?? null;

                    if ($plain_password) {
                        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT); // Hash the password securely

                        // Add 'verified' column to your INSERT statement
                        $stmt = $conn->prepare("INSERT INTO Users (email, password, verified) VALUES (?, ?, 1)"); // Default to 1 (verified)
                        if ($stmt === false) {
                            $response['message'] = "Database error (insert prepare failed): " . $conn->error;
                        } else {
                            $stmt->bind_param("ss", $user_email, $hashed_password);
                            if ($stmt->execute()) {
                                $response['status'] = "success";
                                $response['message'] = "Account created and verified successfully! You can now log in.";
                                $_SESSION['user_id'] = $conn->insert_id;
                                $_SESSION['user_email'] = $user_email;
                                $_SESSION['loggedin'] = true;
                                // I-clear ang signup-specific na data ng session
                                unset($_SESSION['pending_signup_password']); // Clear plain password from session
                                unset($_SESSION['otp_email_for_verification']); // I-clear ang email pagkatapos ng matagumpay na signup
                                unset($_SESSION['is_password_reset_flow']); // I-clear ang flow flag
                                $response['redirect_url'] = "/Dashboard/dashboard.php"; // I-redirect sa dashboard pagkatapos ng matagumpay na signup
                            } else {
                                $response['message'] = "Error creating account: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    } else {
                        $response['message'] = "Missing password data for account creation. Please try signing up again.";
                    }
                }
                $stmt_check->close();
            }

        } elseif ($is_password_reset_flow) { // ✅ PASSWORD RESET FLOW
            $response['status'] = "success";
            $response['message'] = "OTP verified. You can now reset your password.";
            $_SESSION['reset_password_email'] = $user_email; // I-store ang email para sa reset_password.php
            unset($_SESSION['is_password_reset_flow']); // I-clear ang flow flag
            unset($_SESSION['otp_email_for_verification']); // I-clear ang email pagkatapos ng matagumpay na verification
            $response['redirect_url'] = "LoginPage/reset_password.php"; // I-redirect sa password reset page
        } else {
            // Edge case: Tumugma ang OTP, ngunit hindi malinaw ang layunin ng session
            $response['message'] = "OTP verified, but session flow is unclear. Please try again.";
        }

    } else {
        // ❌ Maling OTP
        $response['message'] = "Incorrect OTP. Please try again.";
        // Panatilihing bukas ang modal kung mali ang OTP sa susunod na page load
        $_SESSION['open_otp_modal_on_load'] = true;
    }

} else {
    // ❌ Maling request method (hindi POST)
    $response['message'] = "Invalid request method.";
}

// Magpadala ng JSON response para sa mga AJAX request, kung hindi ay i-redirect
if ($is_ajax_request) {
    echo json_encode($response);
    exit();
} else {
    $_SESSION['otp_verification_message'] = $response['message'];
    $_SESSION['otp_verification_status'] = $response['status'];
    // Para sa direktang access pagkatapos ng verification, ang redirect_url mula sa response ay gagamitin ng Login.js
    // Kung walang redirect_url sa response (hal., sa error), magre-redirect pa rin ito pabalik sa Login.php
    header("Location: ../index.php");
    exit();
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>