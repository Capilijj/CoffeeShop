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

    // Get the specific buttons/links that trigger form changes
    const tabs = document.querySelectorAll('.tab'); // Login/Signup tabs
    const switchToSignupLink = document.getElementById('switchToSignup');
    const switchToLoginLink = document.getElementById('switchToLogin');
    const switchToForgotLink = document.getElementById('switchToForgot'); // New: Forgot Password link
    const backToLoginFromForgotLink = document.getElementById('backToLoginFromForgot'); // New: Back to Login link

    // Function to show a specific form and hide others
    function showForm(formId) {
        allFormContents.forEach(content => content.classList.remove('active')); // Hide all forms
        document.getElementById(formId).classList.add('active'); // Show the target form
    }

    // Function to activate a specific tab (used for Login/Signup)
    function activateTab(tabId) {
        tabs.forEach(t => t.classList.remove('active')); // Deactivate all tabs
        const targetTabButton = document.querySelector(`.tab[data-tab="${tabId}"]`);
        if (targetTabButton) {
            targetTabButton.classList.add('active'); // Activate the specific tab
        }
    }

    // Event listeners for Login/Signup tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetTab = tab.dataset.tab;
            mainTabHeader.classList.remove('hidden'); // Ensure tab header is visible
            activateTab(targetTab);
            showForm(targetTab);
        });
    });

    // Event listeners for switch links (bottom of forms)
    if (switchToSignupLink) {
        switchToSignupLink.addEventListener('click', (e) => {
            e.preventDefault();
            mainTabHeader.classList.remove('hidden'); // Ensure tab header is visible
            activateTab('signup');
            showForm('signup');
        });
    }

    if (switchToLoginLink) {
        switchToLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            mainTabHeader.classList.remove('hidden'); // Ensure tab header is visible
            activateTab('login');
            showForm('login');
        });
    }

    // NEW: Event listener for "Forgot Password?" link
    if (switchToForgotLink) {
        switchToForgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            mainTabHeader.classList.add('hidden'); // Hide the Login/Signup tab header
            allFormContents.forEach(content => content.classList.remove('active')); // Hide all forms
            document.getElementById('forgotPassword').classList.add('active'); // Show Forgot Password form
        });
    }

    // NEW: Event listener for "Back to Login" link from Forgot Password
    if (backToLoginFromForgotLink) {
        backToLoginFromForgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            mainTabHeader.classList.remove('hidden'); // Show the Login/Signup tab header
            activateTab('login'); // Activate the Login tab
            showForm('login'); // Show the Login form
        });
    }


    // --- Navbar Toggle Functionality ---
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.getElementById('navbarNav');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 900) {
                    navLinks.classList.remove('active');
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 900 && navLinks.classList.contains('active')) {
                if (!navLinks.contains(e.target) && !navToggle.contains(e.target)) {
                    navLinks.classList.remove('active');
                }
            }
        });

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth > 900 && navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                }
            }, 250);
        });
    }
});