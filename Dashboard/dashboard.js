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

    // Intersection Observer for section fade-in
    const sectionsToObserve = document.querySelectorAll('#about-us, #contact-us, #products, #services');
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

    sectionsToObserve.forEach(section => {
        observer.observe(section);
    });

    // Services slider functionality
    const slider = document.querySelector('.services-slider-wrapper');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    if (slider && prevBtn && nextBtn) {
        // Find the width of a single service card and the gap between them
        const card = document.querySelector('.service-card');
        const cardWidth = card.offsetWidth;
        const gap = parseInt(window.getComputedStyle(slider).gap);
        const scrollDistance = cardWidth + gap;

        nextBtn.addEventListener('click', () => {
            slider.scrollBy({
                left: scrollDistance,
                behavior: 'smooth'
            });
        });

        prevBtn.addEventListener('click', () => {
            slider.scrollBy({
                left: -scrollDistance,
                behavior: 'smooth'
            });
        });
    }
});