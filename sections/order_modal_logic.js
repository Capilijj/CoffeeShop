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

    // --- PAYMENT TOGGLE FUNCTION ---
    function setPaymentType(type) {
        orderPaymentTypeInput.value = type;
        if (type === 'GCash') {
            gcashBtn.classList.add('selected');
            codBtn.classList.remove('selected');
            gcashSection.style.display = 'block';
            codNote.style.display = 'none';
            orderProofInput.required = true;
            proofSection.style.display = 'block';
        } else if (type === 'COD') {
            codBtn.classList.add('selected');
            gcashBtn.classList.remove('selected');
            gcashSection.style.display = 'none';
            codNote.style.display = 'block';
            orderProofInput.required = false;
            proofSection.style.display = 'none';
        }
    }

    // Set initial payment type and listeners
    setPaymentType(orderPaymentTypeInput.value); 
    gcashBtn.onclick = () => setPaymentType('GCash');
    codBtn.onclick = () => setPaymentType('COD');

    // --- PRICE CALCULATION FUNCTION (GLOBALIZED) ---
    // Ginawang global para matawag sa menu.php pagka-open ng modal
    window.updateAddonPrice = function() {
        const basePrice = parseFloat(basePriceInput.value) || 0;
        const quantity = parseInt(qtyInput.value) || 1;
        let addonTotal = 0;
        
        // I-iterate lang ang mga NAKA-CHECK na inputs
        document.querySelectorAll('#addonsList input:checked').forEach(input => {
            // Tinitingnan lang ang mga input na HINDI disabled
            if (!input.disabled) {
                const price = parseFloat(input.getAttribute('data-price')) || 0;
                addonTotal += price;
            }
        });

        const totalOrderPrice = (basePrice + addonTotal) * quantity;
        
        totalPriceSpan.textContent = `₱${totalOrderPrice.toFixed(2)}`;
        finalPriceInput.value = totalOrderPrice.toFixed(2);
    };

    // Attach listeners to price-changing elements
    qtyInput.addEventListener('change', window.updateAddonPrice);
    if (addonsList) {
        // Gumamit ng 'change' event sa buong listahan para ma-handle ang radio at checkbox
        addonsList.addEventListener('change', window.updateAddonPrice);
    }
    
    // --- MODAL HANDLERS ---
    orderCloseBtn.onclick = function() {
        orderModal.style.display = 'none';
    };

    // Close modal when clicking outside
    orderModal.addEventListener('click', function(e) {
        if (e.target === orderModal) {
            orderModal.style.display = 'none';
        }
    });

    // Handle AJAX form submission
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Simple validation for GCash proof
            if (orderPaymentTypeInput.value === 'GCash' && orderProofInput.required && !orderProofInput.files.length) {
                alert("Please upload proof of payment for GCash.");
                return;
            }
            
            // Re-calculate price one last time before submission
            window.updateAddonPrice();

            const formData = new FormData(this);

            fetch('../Order/save_order.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    orderModal.style.display = 'none';
                    // CRITICAL LOGIC: Ipakita ang Feedback modal muna
                    if (data.show_feedback_prompt) {
                        oneTimeFeedbackModal.classList.add('show');
                    } else {
                        // Kung nakapag-feedback na, diretso sa Order Success
                        orderSuccessModal.classList.add('show');
                    }
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

    // --- FEEDBACK RATING LOGIC ---
    function setRating(rating) {
        feedbackRatingInput.value = rating;
        starElements.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            if (starRating <= rating) {
                star.classList.add('selected');
            } else {
                star.classList.remove('selected');
            }
        });
    }

    starElements.forEach(star => {
        star.addEventListener('click', function() {
            setRating(parseInt(this.getAttribute('data-rating')));
        });
    });

    // Handle Feedback Submission (UPDATED to use save_feedback.php)
    if (oneTimeFeedbackForm) {
        oneTimeFeedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selectedRating = parseInt(feedbackRatingInput.value);
            if (selectedRating === 0) {
                alert("Please select a star rating before submitting.");
                return;
            }
            
            const feedbackData = new FormData(this);

            // Fetch to save_feedback.php
            fetch('../Feedback/save_feedback.php', {
                method: 'POST',
                body: feedbackData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.error("Feedback saving failed but proceeding to order success:", data.message);
                }
            })
            .catch(err => {
                 console.error("Feedback AJAX error:", err);
                 // I-log lang ang error, pero ituloy pa rin sa success modal para sa magandang UX
            })
            .finally(() => {
                // I-reset ang rating UI at itago ang feedback modal
                setRating(0); 
                oneTimeFeedbackModal.classList.remove('show');
                orderSuccessModal.classList.add('show'); // Ipakita ang Order Success
            });
        });
    }

    // Skip Feedback Button Handler
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