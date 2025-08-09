
// Responsive donut-style mobile nav for header
document.addEventListener('DOMContentLoaded', function () {
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    let donutMenu = null;


    function createDonutMenu() {
        if (donutMenu) return donutMenu;
        donutMenu = document.createElement('div');
        donutMenu.className = 'donut-nav-menu';
        // Clone nav links except login/profile
        const links = Array.from(navLinks.querySelectorAll('a:not(.nav-login-btn):not(.nav-profile-btn)'));
        // Add login or profile button as last
        const loginBtn = navLinks.querySelector('.nav-login-btn');
        const profileBtn = navLinks.querySelector('.nav-profile-btn');
        const allLinks = links.slice();
        if (profileBtn) {
            allLinks.push(profileBtn);
        } else if (loginBtn) {
            allLinks.push(loginBtn);
        }
        allLinks.forEach((link, i) => {
            const donutLink = link.cloneNode(true);
            if (profileBtn && link === profileBtn) {
                donutLink.classList.remove('nav-profile-btn');
                donutLink.classList.add('nav-profile-btn');
                // Also clone the profile-circle
                const origCircle = link.querySelector('.profile-circle');
                if (origCircle) {
                    donutLink.innerHTML = '';
                    donutLink.appendChild(origCircle.cloneNode(true));
                }
            } else if (loginBtn && link === loginBtn) {
                donutLink.classList.remove('nav-login-btn');
                donutLink.classList.add('donut-login-btn');
            } else {
                donutLink.classList.add('donut-link');
            }
            donutMenu.appendChild(donutLink);
        });
        document.body.appendChild(donutMenu);
        // Close menu on outside click
        document.addEventListener('click', function(e) {
            if (donutMenu && !donutMenu.contains(e.target) && e.target !== navToggle) {
                donutMenu.classList.remove('show');
            }
        });
        return donutMenu;
    }

    navToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        if (!donutMenu) createDonutMenu();
        donutMenu.classList.toggle('show');
    });

    // Smooth scroll for nav links (desktop and mobile)
    function setupSmoothScroll(container) {
        const scrollLinks = container.querySelectorAll('a[href^="#"]');
        scrollLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href').replace('#', '');
                const target = document.getElementById(targetId);
                if (target) {
                    e.preventDefault();
                    // Account for fixed header height
                    const header = document.querySelector('.header');
                    const headerHeight = header ? header.offsetHeight : 0;
                    const rect = target.getBoundingClientRect();
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const top = rect.top + scrollTop - headerHeight - 10;
                    window.scrollTo({ top, behavior: 'smooth' });
                    if (donutMenu) donutMenu.classList.remove('show');
                }
            });
        });
    }
    // Desktop nav
    setupSmoothScroll(navLinks);
    // Mobile nav (donut menu)
    document.addEventListener('DOMContentLoaded', function() {
        if (donutMenu) setupSmoothScroll(donutMenu);
    });
    // Also re-setup when donut menu is created
    const origCreateDonutMenu = createDonutMenu;
    createDonutMenu = function() {
        const menu = origCreateDonutMenu();
        setupSmoothScroll(menu);
        return menu;
    };

    // Hide donut menu on resize to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 900 && donutMenu) {
            donutMenu.classList.remove('show');
        }
    });
});
