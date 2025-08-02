document.addEventListener('DOMContentLoaded', () => {

    // --- Password Toggle ---
    const togglePasswords = document.querySelectorAll('.toggle-password');
    togglePasswords.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // --- Form Switching Functionality (Login, Signup, Forgot Password) ---
    const mainTabHeader = document.getElementById('mainTabHeader'); // The tab header for Login/Signup
    const allFormContents = document.querySelectorAll('.form-content'); // All forms: login, signup, forgotPassword

    const tabs = document.querySelectorAll('.tab'); // Login/Signup tabs
    const switchToSignupLink = document.getElementById('switchToSignup');
    const switchToLoginLink = document.getElementById('switchToLogin');
    const switchToForgotLink = document.getElementById('switchToForgot');
    const backToLoginFromForgotLink = document.getElementById('backToLoginFromForgot');

    function showForm(formId) {
        allFormContents.forEach(formContent => {
            formContent.classList.remove('active');
        });
        const formToShow = document.getElementById(formId);
        if (formToShow) {
            formToShow.classList.add('active');
        }

        // Adjust tab header visibility
        if (formId === 'forgotPassword') {
            if (mainTabHeader) mainTabHeader.style.display = 'none';
        } else {
            if (mainTabHeader) mainTabHeader.style.display = 'flex';
        }
    }

    // Initial form display based on URL hash or default to login
    const initialHash = window.location.hash.substring(1);
    if (initialHash) {
        if (initialHash === 'signup') {
            showForm('signup');
            tabs.forEach(tab => tab.classList.remove('active'));
            document.querySelector('.tab[data-tab="signup"]').classList.add('active');
        } else if (initialHash === 'forgot') {
            showForm('forgotPassword');
        } else {
            showForm('login');
            document.querySelector('.tab[data-tab="login"]').classList.add('active');
        }
    } else {
        showForm('login');
        // Ensure login tab is active by default
        const loginTab = document.querySelector('.tab[data-tab="login"]');
        if (loginTab) loginTab.classList.add('active');
    }


    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const targetTab = this.getAttribute('data-tab');
            if (targetTab === 'login') {
                showForm('login');
            } else if (targetTab === 'signup') {
                showForm('signup');
            }
        });
    });

    if (switchToSignupLink) {
        switchToSignupLink.addEventListener('click', (e) => {
            e.preventDefault();
            showForm('signup');
            tabs.forEach(tab => tab.classList.remove('active'));
            document.querySelector('.tab[data-tab="signup"]').classList.add('active');
        });
    }

    if (switchToLoginLink) {
        switchToLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            showForm('login');
            tabs.forEach(tab => tab.classList.remove('active'));
            document.querySelector('.tab[data-tab="login"]').classList.add('active');
        });
    }

    if (switchToForgotLink) {
        switchToForgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            showForm('forgotPassword');
        });
    }

    if (backToLoginFromForgotLink) {
        backToLoginFromForgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            showForm('login');
            // Ensure login tab is active when returning
            tabs.forEach(tab => tab.classList.remove('active'));
            const loginTab = document.querySelector('.tab[data-tab="login"]');
            if (loginTab) loginTab.classList.add('active');
        });
    }


    // --- Universal Validation Dialog ---
    const validationDialog = document.getElementById('validationDialog');
    const dialogMessage = document.getElementById('dialogMessage');
    const dialogOkButton = validationDialog ? validationDialog.querySelector('.dialog-ok-button') : null;
    const dialogCloseButton = document.getElementById('dialogCloseButton'); // This is the 'X' button

    if (dialogCloseButton) {
        dialogCloseButton.addEventListener('click', () => {
            // Only close the dialog/modal, do not switch to login or any other tab
            // If OTP modal is open, just close it
            if (otpDialogContent && otpDialogContent.style.display === 'block') {
                showOtpModal(false);
            } else {
                validationDialog.classList.remove('show');
            }
            // Optionally clear message or redirect if it was a success message with redirect
            if (phpRedirectUrl && phpMessage.includes("success")) {
                window.location.href = phpRedirectUrl;
            }
        });
    }

    function showValidationDialog(message, redirectUrl = '') {
        if (validationDialog) {
            dialogMessage.textContent = message;
            // Ensure general dialog content is shown, hide OTP specific
            document.getElementById('dialogContent').querySelector('#otpDialogContent').style.display = 'none';
            document.getElementById('dialogContent').querySelector('p#dialogMessage').style.display = 'block';
            document.getElementById('dialogContent').querySelector('button#dialogOkButton').style.display = 'block';

            validationDialog.classList.add('show');

            // Set up OK button click handler
            const handleOkClick = () => {
                validationDialog.classList.remove('show');
                dialogOkButton.removeEventListener('click', handleOkClick);
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            };
            dialogOkButton.addEventListener('click', handleOkClick);
        }
    }

    // --- Smart error handling for tab/form persistence ---
    if (phpMessage) {
        // Determine which form/tab to show based on the error context
        let showTab = null;
        let setEmail = null;
        // Check for signup error (message and signup email present)
        if (
            phpMessage &&
            document.getElementById('signup-email') &&
            window.phpSignupEmail && window.phpSignupEmail.length > 0 &&
            (
                phpMessage.includes('registration') ||
                phpMessage.includes('Invalid email') ||
                phpMessage.includes('Gmail') ||
                phpMessage.includes('Passwords do not match') ||
                phpMessage.includes('Password must be at least') ||
                phpMessage.includes('already registered') ||
                phpMessage.includes('fill out the signup form')
            )
        ) {
            showTab = 'signup';
            setEmail = window.phpSignupEmail;
        }
        // Check for forgot password error
        if (
            phpMessage &&
            document.getElementById('forgot-email') &&
            window.phpForgotEmail && window.phpForgotEmail.length > 0 &&
            (
                phpMessage.includes('No account found') ||
                phpMessage.includes('valid email') ||
                phpMessage.includes('Please enter your email')
            )
        ) {
            showTab = 'forgotPassword';
            setEmail = window.phpForgotEmail;
        }
        // Show the correct tab if needed
        if (showTab) {
            showForm(showTab);
            tabs.forEach(tab => tab.classList.remove('active'));
            if (showTab === 'signup') {
                document.querySelector('.tab[data-tab="signup"]').classList.add('active');
                if (setEmail) document.getElementById('signup-email').value = setEmail;
            }
            if (showTab === 'forgotPassword') {
                if (setEmail) document.getElementById('forgot-email').value = setEmail;
            }
        }
        showValidationDialog(phpMessage, phpRedirectUrl);
    }

    // --- OTP Modal Functionality ---
    const otpDialogContent = document.getElementById('otpDialogContent');
    const otpMessageBox = document.getElementById('otpMessageBox');
    const otpEmailDisplay = document.getElementById('otpEmailDisplay');
    const otpInputs = document.querySelectorAll('.otp-box');
    const otpVerificationForm = document.getElementById('otpVerificationForm');
    const verifyOtpButton = document.getElementById('verifyOtpButton');
    const resendOtpForm = document.getElementById('resendOtpForm');
    const resendOtpButton = document.getElementById('resendOtpButton');
    const otpTimerDisplay = document.getElementById('otpTimer');
    let otpTimerInterval;
    const OTP_LIFETIME = 120; // 5 minutes in seconds

    // Function to show/hide OTP message within the modal
    function showOtpMessage(message, type) {
        otpMessageBox.textContent = message;
        otpMessageBox.className = 'message-box';
        if (type) {
            otpMessageBox.classList.add(type);
        }
        if (message) {
            otpMessageBox.style.display = 'block';
        } else {
            otpMessageBox.style.display = 'none';
        }
    }

    // Function to open/close OTP modal (now referring to otpDialogContent visibility)
    function showOtpModal(show) {
        if (validationDialog) {
            if (show) {
                validationDialog.classList.add('show');
                if (otpDialogContent) {
                    otpDialogContent.style.display = 'block';
                    // Hide general dialog parts
                    document.getElementById('dialogContent').querySelector('p#dialogMessage').style.display = 'none';
                    document.getElementById('dialogContent').querySelector('button#dialogOkButton').style.display = 'none';
                }
                otpInputs[0].focus();
            } else {
                validationDialog.classList.remove('show');
                if (otpDialogContent) {
                    otpDialogContent.style.display = 'none';
                }
                stopOtpTimer();
                otpInputs.forEach(box => box.value = '');
                showOtpMessage('');
            }
        }
    }

    // OTP Input auto-focus and validation
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            // Allow only numbers
            input.value = input.value.replace(/[^0-9]/g, '');

            if (input.value.length === input.maxLength) {
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                } else {
                    // Last input, can trigger verification if desired
                }
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value.length === 0) {
                if (index > 0) {
                    otpInputs[index - 1].focus();
                }
            }
        });
    });

    // OTP Timer
    let timeLeft = OTP_LIFETIME;

    function startOtpTimer(initialTimeLeft = OTP_LIFETIME) {
        stopOtpTimer();
        timeLeft = initialTimeLeft > 0 ? initialTimeLeft : OTP_LIFETIME;
        resendOtpButton.disabled = true;
        resendOtpButton.textContent = `Resend OTP in ${formatTime(timeLeft)}`;

        otpTimerInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft <= 0) {
                stopOtpTimer();
                resendOtpButton.disabled = false;
                resendOtpButton.textContent = 'Resend OTP';
                showOtpMessage("OTP has expired. Please resend.", "error");
            } else {
                resendOtpButton.textContent = `Resend OTP in ${formatTime(timeLeft)}`;
            }
        }, 1000);
    }

    function stopOtpTimer() {
        clearInterval(otpTimerInterval);
        otpTimerInterval = null;
    }

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    // Verify OTP function (AJAX)
    async function verifyOtp(event) {
        event.preventDefault();

        const userEnteredOtp = Array.from(otpInputs).map(input => input.value).join('');

        if (userEnteredOtp.length !== 6 || isNaN(userEnteredOtp)) {
            showOtpMessage("Please enter a valid 6-digit OTP.", "error");
            return;
        }

        verifyOtpButton.disabled = true;
        showOtpMessage("Verifying OTP...", "info");

        try {
            const formData = new FormData(otpVerificationForm);
            formData.append('otp', userEnteredOtp);
            formData.append('is_password_reset_flow', phpIsPasswordResetFlow ? 'true' : 'false');


            const response = await fetch("LoginPage/verify_otp.php", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            showOtpMessage(data.message, data.status);

            if (data.status === 'success') {
                stopOtpTimer();
                setTimeout(() => {
                    showOtpModal(false);
                    // If this is a password reset flow, show the reset password form as a tab/modal
                    if (phpIsPasswordResetFlow || (data.redirect_url && data.redirect_url.includes('reset_password.php'))) {
                        // Show a reset password form/modal/tab
                        showResetPasswordTab();
                    } else if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    }
                }, 1500);
            }
    // --- Show Reset Password Tab/Modal Logic ---
    function showResetPasswordTab() {
        // Hide all forms and tabs
        allFormContents.forEach(formContent => formContent.classList.remove('active'));
        if (mainTabHeader) mainTabHeader.style.display = 'none';

        // Check if reset password form already exists
        let resetForm = document.getElementById('resetPasswordTab');
        if (!resetForm) {
            // Create the reset password form dynamically
            resetForm = document.createElement('div');
            resetForm.className = 'login-box form-content active';
            resetForm.id = 'resetPasswordTab';
            resetForm.innerHTML = `
                <h2>Reset Your Password</h2>
                <form id="resetPasswordForm" autocomplete="off">
                    <div class="input-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required minlength="8">
                    </div>
                    <div class="input-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required minlength="8">
                    </div>
                    <button type="submit" class="login-btn">Reset Password</button>
                    <div class="back-to-login" style="margin-top:10px;">
                        <a href="#" id="backToLoginFromReset">Back to Login</a>
                    </div>
                    <div id="resetPasswordMessage" class="error-message" style="display:none;"></div>
                </form>
            `;
            document.querySelector('.container').appendChild(resetForm);
        } else {
            resetForm.classList.add('active');
        }

        // Add event listener for reset password form
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const resetPasswordMessage = document.getElementById('resetPasswordMessage');
        const backToLoginFromReset = document.getElementById('backToLoginFromReset');
        if (resetPasswordForm) {
            resetPasswordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const newPass = document.getElementById('new_password').value;
                const confirmPass = document.getElementById('confirm_password').value;
                resetPasswordMessage.style.display = 'none';
                resetPasswordMessage.textContent = '';
                if (!newPass || !confirmPass) {
                    resetPasswordMessage.textContent = 'Please fill in both password fields.';
                    resetPasswordMessage.style.display = 'block';
                    return;
                }
                if (newPass !== confirmPass) {
                    resetPasswordMessage.textContent = 'Passwords do not match.';
                    resetPasswordMessage.style.display = 'block';
                    return;
                }
                if (newPass.length < 8) {
                    resetPasswordMessage.textContent = 'Password must be at least 8 characters.';
                    resetPasswordMessage.style.display = 'block';
                    return;
                }
                // AJAX to reset_password.php
                try {
                    const formData = new FormData();
                    formData.append('new_password', newPass);
                    formData.append('confirm_password', confirmPass);
                    const response = await fetch('LoginPage/reset_password.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.text();
                    if (data.includes('success')) {
                        resetPasswordMessage.textContent = 'Password reset successful! You can now log in.';
                        resetPasswordMessage.style.display = 'block';
                        setTimeout(() => {
                            // Remove reset form and show login
                            resetForm.remove();
                            showForm('login');
                            if (mainTabHeader) mainTabHeader.style.display = 'flex';
                        }, 2000);
                    } else {
                        // Try to extract error message from response
                        let msg = data;
                        try {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(data, 'text/html');
                            const err = doc.querySelector('.error-message');
                            if (err) msg = err.textContent;
                        } catch {}
                        resetPasswordMessage.textContent = msg || 'Error updating password. Please try again.';
                        resetPasswordMessage.style.display = 'block';
                    }
                } catch (error) {
                    resetPasswordMessage.textContent = 'Error updating password. Please try again.';
                    resetPasswordMessage.style.display = 'block';
                }
            });
        }
        if (backToLoginFromReset) {
            backToLoginFromReset.addEventListener('click', function(e) {
                e.preventDefault();
                resetForm.remove();
                showForm('login');
                if (mainTabHeader) mainTabHeader.style.display = 'flex';
            });
        }
    }
        } catch (error) {
            console.error('Error during OTP verification:', error);
            showOtpMessage("An error occurred during verification. Please try again.", "error");
        } finally {
            verifyOtpButton.disabled = false;
        }
    }


    // Resend OTP function (AJAX)
    async function resendOtp(event) {
        event.preventDefault();

        resendOtpButton.disabled = true;
        showOtpMessage("Requesting new OTP...", "info");

        try {
            const formData = new FormData();
            formData.append('action', 'resend_otp');
            formData.append('email', phpOtpEmailForVerification);
            formData.append('is_password_reset_flow', phpIsPasswordResetFlow ? 'true' : 'false');


            const response = await fetch("LoginPage/send_otp_email.php", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            showOtpMessage(data.message, data.status);

            if (data.status === 'success') {
                startOtpTimer();
                otpInputs.forEach(box => box.value = '');
                otpInputs[0].focus();
            } else if (data.status === 'info') {
                startOtpTimer(data.time_remaining);
                otpInputs.forEach(box => box.value = '');
                otpInputs[0].focus();
            } else {
                if (!otpTimerInterval) {
                    resendOtpButton.disabled = false;
                }
            }
        } catch (error) {
            console.error('Error during OTP resend:', error);
            showOtpMessage("An error occurred during resend. Please try again.", "error");
        } finally {
            if (!otpTimerInterval && resendOtpButton.disabled) {
                resendOtpButton.disabled = false;
            }
        }
    }


    // Attach event listeners for OTP modal buttons
    if (verifyOtpButton) {
        otpVerificationForm.addEventListener('submit', verifyOtp);
    }
    if (resendOtpForm) {
        resendOtpForm.addEventListener('submit', resendOtp);
    }

    // Check if OTP modal needs to be opened on page load (from PHP session)
    if (phpOpenOtpModal) {
        showOtpModal(true);
        otpEmailDisplay.textContent = phpOtpEmailForVerification;

        if (phpOtpVerificationMessage) {
            showOtpMessage(phpOtpVerificationMessage, phpOtpVerificationStatus);
        }

        startOtpTimer(OTP_LIFETIME);
    }
});