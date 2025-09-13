// Responsive donut-style mobile nav for header
document.addEventListener('DOMContentLoaded', function () {
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    let donutMenu = null;

    function createDonutMenu() {
        if (donutMenu) return donutMenu;
        donutMenu = document.createElement('div');
        donutMenu.className = 'donut-nav-menu';
        
        // Clone nav links from the main navigation
        const allLinks = Array.from(navLinks.querySelectorAll('a'));
        
        allLinks.forEach(link => {
            const donutLink = link.cloneNode(true);
            
            // Adjust specific classes for mobile menu items
            if (donutLink.classList.contains('nav-login-btn')) {
                donutLink.classList.add('donut-login-btn');
                donutLink.classList.remove('nav-login-btn');
            } else if (donutLink.classList.contains('nav-profile-btn')) {
                // For the profile button, ensure the circle is cloned correctly
                const origCircle = link.querySelector('.profile-circle');
                if (origCircle) {
                    donutLink.innerHTML = '';
                    donutLink.appendChild(origCircle.cloneNode(true));
                }
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
        if (!donutMenu) {
            createDonutMenu();
            setupSmoothScroll(donutMenu);
        }
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

    // Desktop nav smooth scroll
    setupSmoothScroll(navLinks);

    // Hide donut menu on resize to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 900 && donutMenu) {
            donutMenu.classList.remove('show');
        }
    });
});