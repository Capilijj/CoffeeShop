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
                <form action="../index.php" method="POST">
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
                <form action="../index.php" method="POST" id="signupForm">

                    <div class="input-group">
                        <label for="signup-name">Name</label>
                        <input type="text" id="signup-name" name="signup_name" placeholder="Enter your name" required autocomplete="name" />
                    </div>
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
                <form action="../index.php" method="POST">
                    <div class="input-group">
                        <label for="forgot-email">Email</label>
                        <input type="email" id="forgot-email" name="forgot_email" placeholder="Enter your Email" required autocomplete="email" />
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

            <form id="otpVerificationForm" method="post" action="LoginPage/verify_otp.php">
                <div class="otp-input-group-boxes">
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
            <form id="resendOtpForm" method="post" action="LoginPage/send_otp_email.php" style="margin-top: 10px;">
                <button type="submit" class="dialog-ok-button resend-link" id="resendOtpButton">Resend OTP</button>
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

    // Pass signup/forgot email to JS for persistence on error
    window.phpSignupEmail = <?php echo json_encode($_POST['signup_email'] ?? ''); ?>;
    window.phpForgotEmail = <?php echo json_encode($_POST['forgot_email'] ?? ''); ?>;
</script>
