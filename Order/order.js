document.addEventListener('DOMContentLoaded', function() {
    // Mobile nav menu toggle
    const navToggle = document.querySelector('.nav-toggle');
    const donutNavMenu = document.querySelector('.donut-nav-menu');

    navToggle.addEventListener('click', function() {
        donutNavMenu.classList.toggle('show');
    });

    // Smooth scroll for nav links
    document.querySelectorAll('.donut-link').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
                // Close the menu after clicking a link
                donutNavMenu.classList.remove('show');
            }
        });
    });

    // Intersection Observer for services section fade-in
    const servicesSection = document.getElementById('services');
    if (servicesSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Stop observing after the transition is triggered once
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5 // Triggers when 50% of the element is visible
        });

        observer.observe(servicesSection);
    }
});

// Order system popup logic
document.addEventListener('DOMContentLoaded', function() {
    const orderButtons = document.querySelectorAll('.order-menu-btn');
    const orderModal = document.getElementById('orderModal');
    const closeBtn = document.getElementById('orderCloseBtn');
    const menuName = document.getElementById('orderMenuName');
    const menuDesc = document.getElementById('orderMenuDesc');
    const menuPrice = document.getElementById('orderMenuPrice');
    const menuImg = document.getElementById('orderMenuImg');
    const menuInput = document.getElementById('orderMenuInput');

    orderButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const name = btn.getAttribute('data-name');
            const desc = btn.getAttribute('data-desc');
            const img = btn.getAttribute('data-img');
            menuName.textContent = name;
            menuDesc.textContent = desc;
            menuPrice.textContent = '₱25';
            menuImg.src = img;
            menuInput.value = name;
            orderModal.style.display = 'flex';
        });
    });

    closeBtn.addEventListener('click', function() {
        orderModal.style.display = 'none';
    });

    // Close modal on outside click
    orderModal.addEventListener('click', function(e) {
        if (e.target === orderModal) {
            orderModal.style.display = 'none';
        }
    });
});