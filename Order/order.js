document.addEventListener('DOMContentLoaded', function() {
    // --- ELEMENT REFERENCES ---
    const orderModal = document.getElementById('orderModal');
    const orderSuccessModal = document.getElementById('orderSuccessModal');
    // NEW FEEDBACK ELEMENTS
    const oneTimeFeedbackModal = document.getElementById('oneTimeFeedbackModal');
    const oneTimeFeedbackForm = document.getElementById('oneTimeFeedbackForm');
    const skipFeedbackBtn = document.getElementById('skipFeedbackBtn');
    const ratingStarsContainer = document.querySelector('#oneTimeFeedbackModal .rating-stars');
    const starElements = ratingStarsContainer ? ratingStarsContainer.querySelectorAll('.star') : [];
    const feedbackRatingInput = document.getElementById('feedbackRatingInput');
    
    const orderForm = document.getElementById('orderForm');
    const orderCloseBtn = document.getElementById('orderCloseBtn');
    const orderSuccessOkBtn = document.getElementById('orderSuccessOkBtn');
    
    // Payment Elements
    const gcashBtn = document.getElementById('gcashBtn');
    const codBtn = document.getElementById('codBtn');
    const gcashSection = document.getElementById('gcashSection');
    const proofSection = document.getElementById('proofSection');
    const codNote = document.getElementById('codNote');
    const orderPaymentTypeInput = document.getElementById('orderPaymentType');
    const orderProofInput = document.getElementById('orderProof');

    // Price & Add-on Elements
    const basePriceInput = document.getElementById('orderMenuBasePriceInput');
    const basePriceDisplay = document.getElementById('orderMenuBasePriceDisplay');
    const qtyInput = document.getElementById('orderQty');
    const totalPriceSpan = document.getElementById('orderTotalPrice');
    const finalPriceInput = document.getElementById('orderFinalPriceInput');
    const addonsList = document.getElementById('addonsList');
    
    // --- Helper for Mobile Nav/Scroll (kept for continuity) ---
    const navToggle = document.querySelector('.nav-toggle');
    const donutNavMenu = document.querySelector('.donut-nav-menu');
    if (navToggle && donutNavMenu) {
        navToggle.addEventListener('click', function() {
            donutNavMenu.classList.toggle('show');
        });
    }

    document.querySelectorAll('.donut-link').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
                if (donutNavMenu) {
                    donutNavMenu.classList.remove('show');
                }
            }
        });
    });

    // --- PAYMENT TOGGLE FUNCTION ---
    function setPaymentType(type) {
        orderPaymentTypeInput.value = type;
        
        if (type === 'GCash') {
            gcashSection.style.display = 'block';
            proofSection.style.display = 'block';
            codNote.style.display = 'none';
            if (proofSection.style.display === 'block') {
                 orderProofInput.required = true;
            }
            gcashBtn.style.backgroundColor = '#388e3c';
            codBtn.style.backgroundColor = '#A0522D';
        } else if (type === 'COD') {
            gcashSection.style.display = 'none';
            proofSection.style.display = 'none';
            codNote.style.display = 'block';
            orderProofInput.required = false;
            gcashBtn.style.backgroundColor = '#A0522D';
            codBtn.style.backgroundColor = '#388e3c';
        }
    }

    // Set default payment type on page load
    setPaymentType('GCash'); 

    // Add listeners for the buttons
    if(gcashBtn) gcashBtn.addEventListener('click', function() {
        setPaymentType('GCash');
    });

    if(codBtn) codBtn.addEventListener('click', function() {
        setPaymentType('COD');
    });
    
    // --- PRICE CALCULATION FUNCTION ---
    function calculateTotalPrice() {
        let basePrice = parseFloat(basePriceInput.value) || 0;
        let quantity = parseInt(qtyInput.value) || 1;
        let addonTotal = 0;

        document.querySelectorAll('.addon-input:checked').forEach(function(input) {
            if (input.value !== 'none') { 
                addonTotal += parseFloat(input.getAttribute('data-price')) || 0;
            }
        });

        let finalTotal = (basePrice + addonTotal) * quantity;
        
        basePriceDisplay.textContent = basePrice.toFixed(2);
        totalPriceSpan.textContent = finalTotal.toFixed(2);
        finalPriceInput.value = finalTotal.toFixed(2);
    }
    
    // Listeners for quantity and add-ons
    if (qtyInput) {
        qtyInput.addEventListener('change', calculateTotalPrice);
        qtyInput.addEventListener('input', calculateTotalPrice);
    }
    
    if (addonsList) {
        addonsList.addEventListener('change', function(e) {
            if (e.target.classList.contains('addon-input')) {
                calculateTotalPrice();
            }
        });
    }

    // --- NEW STAR RATING LOGIC ---
    function setRating(rating) {
        if (!ratingStarsContainer) return;
        ratingStarsContainer.setAttribute('data-current-rating', rating);
        feedbackRatingInput.value = rating;
        starElements.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            star.classList.toggle('selected', starRating <= rating);
        });
    }

    if (ratingStarsContainer) {
        ratingStarsContainer.addEventListener('click', function(e) {
            const star = e.target.closest('.star');
            if (star) {
                setRating(parseInt(star.getAttribute('data-rating')));
            }
        });

        ratingStarsContainer.addEventListener('mouseover', function(e) {
            const star = e.target.closest('.star');
            if (star) {
                const hoverRating = parseInt(star.getAttribute('data-rating'));
                starElements.forEach(s => {
                    const sRating = parseInt(s.getAttribute('data-rating'));
                    s.classList.toggle('selected', sRating <= hoverRating);
                });
            }
        });

        ratingStarsContainer.addEventListener('mouseout', function() {
            const currentRating = parseInt(ratingStarsContainer.getAttribute('data-current-rating'));
            setRating(currentRating);
        });
        setRating(0); 
    }
    // --- END NEW STAR RATING LOGIC ---

    // --- MODAL & ORDER BUTTON LOGIC ---
    document.querySelectorAll('.order-menu-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            // ... (Iyong existing code para mag-populate ng modal data) ...
            // If you are using this file, you need the logic from menu.php here to populate inputs/displays

            calculateTotalPrice();
            setPaymentType('GCash'); 
            
            if (orderModal) {
                 orderModal.style.display = 'flex'; 
            }
        });
    });

    if (orderCloseBtn) {
        orderCloseBtn.addEventListener('click', function() {
            if (orderModal) orderModal.style.display = 'none';
        });
    }

    if (orderModal) {
        orderModal.addEventListener('click', function(e) {
            if (e.target === orderModal) {
                orderModal.style.display = 'none';
            }
        });
    }
    
    // Handle AJAX form submission (UPDATED)
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // ... (Your existing validation logic for GCash/Proof) ...

            const formData = new FormData(this);
            
            // TANGGALIN ang 'none' value mula sa FormData bago i-send
            const keysToDelete = [];
            for (let [key, value] of formData.entries()) {
                if (value === 'none') {
                    keysToDelete.push(key);
                }
            }
            keysToDelete.forEach(key => formData.delete(key));


            fetch('../Order/save_order.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (orderModal) orderModal.style.display = 'none';
                    
                    // --- UPDATED LOGIC ---
                    if (data.show_feedback_prompt && oneTimeFeedbackModal) {
                         oneTimeFeedbackModal.classList.add('show');
                         setRating(0); // Reset rating input
                    } else if (orderSuccessModal) {
                         orderSuccessModal.classList.add('show');
                    }
                    // ---------------------

                } else {
                    alert(data.message || 'Order failed. Please try again.');
                    console.error('Order failed details:', data);
                }
            })
            .catch(err => {
                alert('Order failed: Network or Server Error.');
                console.error('Order AJAX error:', err);
            });
        });
    }

    // Feedback Submission Handlers (UPDATED)
    if (oneTimeFeedbackForm) {
        oneTimeFeedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (parseInt(feedbackRatingInput.value) === 0) {
                alert("Please select a star rating before submitting.");
                return;
            }
            
            // NOTE: The actual fetch to save_feedback.php should be here.
            
            // Proceed to show success modal regardless of fetch status for good UX
            setRating(0); 
            oneTimeFeedbackModal.classList.remove('show');
            orderSuccessModal.classList.add('show');
        });
    }

    if (skipFeedbackBtn) {
        skipFeedbackBtn.onclick = function() {
            setRating(0);
            oneTimeFeedbackModal.classList.remove('show');
            orderSuccessModal.classList.add('show');
        };
    }
    
    // Redirection Handler
    if (orderSuccessOkBtn) {
        orderSuccessOkBtn.onclick = function() {
            // Redirect to dashboard or orders page
            window.location.href = '../Dashboard/dashboard.php';
        };
    }
});