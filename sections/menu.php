
<?php
// FIX: Gumamit ng conditional session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../LoginPage/database_connection.php';
$user_id = $_SESSION['user_id'] ?? null;
// You might want to add a check here to redirect users who are not logged in:
// if (!$user_id) { header('Location: ../LoginPage/Login.php'); exit(); }

// ----------------------------------------------------
// 1. Fetch Menu Items 
// ----------------------------------------------------
$menu_items = [];
$sql = "SELECT menuid, menuname, description, price, imageurl FROM menu ORDER BY menuid ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}

// ----------------------------------------------------
// 2. Fetch Add-on Options 
//    CRITICAL UPDATE: is_available is now fetched and used to style the UI
// ----------------------------------------------------
$addon_options = [];
// ORDER BY: Inuna ang Size, Sugar Level, at Temperature
$addon_result = $conn->query("SELECT id, name, category, extra_price, is_available FROM addon_options ORDER BY 
    CASE category 
        WHEN 'Size' THEN 1 
        WHEN 'Sugar Level' THEN 2 
        WHEN 'Temperature' THEN 3
        ELSE 4 
    END, name ASC");

if ($addon_result) {
    while ($row = $addon_result->fetch_assoc()) {
        $addon_options[$row['category']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Menu</title>
    <link rel="stylesheet" href="../Order/order.css">
    <link rel="stylesheet" href="../LoginPage/core.css">
    <link rel="stylesheet" href="../Order/order-success.css">
    <link rel="stylesheet" href="./menu.css"> 
    </head>
<body>

<div class="menu-section" id="products">
    <h2>Our Menu</h2>
    <div class="card-container">
        <?php if (empty($menu_items)): ?>
            <div style="color:#A0522D;text-align:center;width:100%;">No menu items found.</div>
        <?php else: ?>
            <?php foreach ($menu_items as $item): ?>
                <div class="menu-card">
                    <img src="<?= htmlspecialchars($item['imageurl']) ?>" alt="<?= htmlspecialchars($item['menuname']) ?>">
                    <h3><?= htmlspecialchars($item['menuname']) ?></h3>
                    <p><?= htmlspecialchars($item['description']) ?></p>
                    <div style="font-weight:600;color:#A0522D;margin-bottom:8px;">₱<?= number_format($item['price'], 2) ?></div>
                    <button class="order-menu-btn" 
                        data-menuid="<?= htmlspecialchars($item['menuid']) ?>"
                        data-name="<?= htmlspecialchars($item['menuname']) ?>" 
                        data-desc="<?= htmlspecialchars($item['description']) ?>" 
                        data-img="<?= htmlspecialchars($item['imageurl']) ?>"
                        data-price="<?= htmlspecialchars($item['price']) ?>"
                    >Order Now</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div id="orderSuccessModal" class="order-success-modal">
    <div class="order-success-content">
        <div class="order-success-title" style="color:#388e3c;">Order placed successfully!</div>
        <button id="orderSuccessOkBtn" class="order-success-btn">Back to Dashboard</button>
    </div>
</div>

<div id="oneTimeFeedbackModal" class="order-success-modal">
    <div class="order-success-content">
        <div class="order-success-title">Tell Us About Your Experience!</div>
        <p>Before you go, can you give us quick feedback on your ordering experience? It's just a one-time thing!</p>
        
        <form id="oneTimeFeedbackForm" action="../Feedback/save_feedback.php" method="POST"> 
            <div class="rating-stars" data-current-rating="0">
                <span class="star" data-rating="1">&#9733;</span>
                <span class="star" data-rating="2">&#9733;</span>
                <span class="star" data-rating="3">&#9733;</span>
                <span class="star" data-rating="4">&#9733;</span>
                <span class="star" data-rating="5">&#9733;</span>
            </div>
            
            <input type="hidden" name="rating" id="feedbackRatingInput" value="0" required>
            
            <textarea name="feedback_text" rows="4" placeholder="Your comments (optional but appreciated)..."></textarea>
            
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
            <input type="hidden" name="feedback_type" value="One-time Prompt">
            
            <button type="submit" class="order-success-btn">Submit Feedback</button>
            <button type="button" id="skipFeedbackBtn" class="order-success-btn">Skip for Now</button>
        </form>
    </div>
</div>


<div id="orderModal">
    <div class="order-modal-content">
        <button id="orderCloseBtn">&times;</button>
        <img id="orderMenuImg" class="order-menu-img" src="" alt="Menu Image">
        <div class="order-menu-title" id="orderMenuName">Menu Name</div>
        <div class="order-menu-desc" id="orderMenuDesc">Menu Description</div>
        
        <form id="orderForm" action="../Order/save_order.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
            <input type="hidden" name="menuid" id="orderMenuIdInput" value="">
            <input type="hidden" name="menu" id="orderMenuInput" value="">
            <input type="hidden" name="base_price" id="orderMenuBasePriceInput" value="0.00">
            <input type="hidden" name="total_price" id="orderFinalPriceInput" value="0.00"> 
            <input type="hidden" name="payment_type" id="orderPaymentType" value="GCash"> 
            <div class="qty-group">
                <label for="orderQty">Quantity:</label>
                <input type="number" name="quantity" id="orderQty" value="1" min="1" max="99">
            </div>

            <div class="add-ons-section">
                <h4 style="color:#A0522D; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Customize Your Drink:</h4>
                <div id="addonsList">
                    <?php
                    foreach ($addon_options as $category => $addons) {
                        echo '<div class="addon-category-group">';
                        echo '  <h5 class="addon-category-title">' . htmlspecialchars($category) . '</h5>';
                        
                        $is_single_select = in_array($category, ['Size', 'Sugar Level', 'Temperature']); 
                        $group_name = str_replace(' ', '_', $category);

                        // Add "None / Default" radio option for single-select groups
                        if ($is_single_select) {
                            echo '<div class="addon-option-row">';
                            echo '  <label class="addon-label">';
                            // Value "none" is used to be filtered out by JS/PHP
                            echo '      <input type="radio" name="addons[' . $group_name . ']" value="none" data-price="0.00" class="addon-input" checked>';
                            echo '      None / Default';
                            echo '  </label>';
                            echo '  <span class="addon-price">₱0.00</span>';
                            echo '</div>';
                        }
                        foreach ($addons as $addon) {
                            $addon_name = htmlspecialchars($addon['name']);
                            $addon_id = htmlspecialchars($addon['id']);
                            $addon_price_display = number_format($addon['extra_price'], 2);
                            $input_type = $is_single_select ? 'radio' : 'checkbox';

                            // Name for radio buttons: addons[CategoryName], Name for checkboxes: addons[]
                            $input_name = $is_single_select ? 'addons[' . $group_name . ']' : 'addons[]';
                            
                            $is_available = (int)$addon['is_available']; // 1 or 0
                            $out_of_stock_class = $is_available === 0 ? 'out-of-stock-addon' : '';
                            $disabled_attr = $is_available === 0 ? 'disabled' : '';
                            
                            $checked = '';
                            
                            echo '<div class="addon-option-row addon-option ' . $out_of_stock_class . '">'; // Added addon-option class for consistency
                            echo '  <label class="addon-label">';
                            echo '      <input type="' . $input_type . '" name="' . $input_name . '" value="' . $addon_id . '" data-price="' . $addon['extra_price'] . '" class="addon-input" ' . $checked . ' ' . $disabled_attr . '>';
                            echo '      ' . $addon_name;
                            if ($is_available === 0) {
                                echo ' <span class="stock-alert">(Out of Stock)</span>'; // Out of Stock label
                            }
                            echo '  </label>';
                            echo '  <span class="addon-price">' . ($addon['extra_price'] > 0 ? '(+₱' . $addon_price_display . ')' : '₱0.00') . '</span>';
                            echo '</div>';
                        }
                        echo '</div>'; // close addon-category-group
                    }
                    ?>
                </div>
            </div>
            <div class="total-price-display">
                Base: ₱<span id="orderMenuBasePriceDisplay">0.00</span> + Customization = TOTAL: ₱<span id="orderTotalPrice">0.00</span>
            </div>
            
            <div id="paymentOptions" style="margin-top: 15px; margin-bottom: 12px;">
                <button type="button" id="gcashBtn" class="order-submit-btn" style="background:#388e3c;margin-right:8px;">GCash</button>
                <button type="button" id="codBtn" class="order-submit-btn" style="background:#A0522D;">Cash on Delivery</button>
            </div>
            
            <div class="order-gcash" id="gcashSection" style="display: block;">
                <label>Pay via GCash:</label><br>
                <img src="../Image/QR.jpg" alt="GCash QR Code"> 
            </div>
            
            <div class="order-upload" id="proofSection" style="display: block;">
                <label for="orderProof">Upload Proof of Payment:</label>
                <input type="file" name="proof" id="orderProof" accept="image/*" required>
            </div>
            
            <div id="codNote" style="display:none;color:#388e3c;font-weight:600;margin-bottom:10px;">You selected Cash on Delivery. Please prepare payment upon delivery.</div>
            
            <button type="submit" class="order-submit-btn" style="background-color: #A0522D; margin-top: 10px;">Place Order</button>
        </form>
    </div>
</div>

<div id="orderBlockPopup" class="order-block-popup" style="display:none;">
    <div class="order-block-content">
        <h2>Complete Your Profile</h2>
        <p>You must provide your address and contact number before placing an order.</p>
        <button id="goToProfileBtn">Go to Profile</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Modal/Element References ---
        const orderModal = document.getElementById('orderModal');
        const orderButtons = document.querySelectorAll('.order-menu-btn');
        const closeBtn = document.getElementById('orderCloseBtn');
        const orderForm = document.getElementById('orderForm');
        const orderSuccessModal = document.getElementById('orderSuccessModal');
        const orderSuccessOkBtn = document.getElementById('orderSuccessOkBtn');
        const orderBlockPopup = document.getElementById('orderBlockPopup');
        const goToProfileBtn = document.getElementById('goToProfileBtn');

        // NEW FEEDBACK ELEMENTS
        const oneTimeFeedbackModal = document.getElementById('oneTimeFeedbackModal');
        const oneTimeFeedbackForm = document.getElementById('oneTimeFeedbackForm');
        const skipFeedbackBtn = document.getElementById('skipFeedbackBtn'); 
        const ratingStarsContainer = document.querySelector('#oneTimeFeedbackModal .rating-stars');
        const starElements = ratingStarsContainer ? ratingStarsContainer.querySelectorAll('.star') : [];
        const feedbackRatingInput = document.getElementById('feedbackRatingInput');


        // --- Form Inputs ---
        const orderMenuIdInput = document.getElementById('orderMenuIdInput');
        const orderMenuInput = document.getElementById('orderMenuInput');
        const orderMenuBasePriceInput = document.getElementById('orderMenuBasePriceInput');
        const orderFinalPriceInput = document.getElementById('orderFinalPriceInput');
        const orderQtyInput = document.getElementById('orderQty');
        const orderProofInput = document.getElementById('orderProof');
        const orderPaymentTypeInput = document.getElementById('orderPaymentType');

        // --- Display Elements ---
        const orderMenuNameDisplay = document.getElementById('orderMenuName');
        const orderMenuDescDisplay = document.getElementById('orderMenuDesc');
        const orderMenuImgDisplay = document.getElementById('orderMenuImg');
        const orderMenuBasePriceDisplay = document.getElementById('orderMenuBasePriceDisplay');
        const orderTotalPriceDisplay = document.getElementById('orderTotalPrice');

        // --- Payment Sections ---
        const gcashBtn = document.getElementById('gcashBtn');
        const codBtn = document.getElementById('codBtn');
        const gcashSection = document.getElementById('gcashSection');
        const proofSection = document.getElementById('proofSection');
        const codNote = document.getElementById('codNote');
        
        let hasAddressAndContact = false;

        // --- Core Functions ---

        /** I-format ang presyo sa 2 decimal places. */
        function formatPrice(price) {
            return parseFloat(price).toFixed(2);
        }

        /** I-update ang total price batay sa base price, add-ons, at quantity. */
        function updateTotalPrice() {
            let basePrice = parseFloat(orderMenuBasePriceInput.value || 0);
            let quantity = parseInt(orderQtyInput.value || 1);
            if (quantity < 1) {
                quantity = 1;
                orderQtyInput.value = 1;
            }

            let addonTotal = 0;
            const addonInputs = document.querySelectorAll('.addon-input:checked');
            
            addonInputs.forEach(input => {
                // Tiyakin na ang addon ay HINDI disabled (out of stock)
                if (input.hasAttribute('disabled')) {
                    // Huwag isama sa total kung disabled
                    return; 
                }
                
                const addonValue = input.value;
                const addonPrice = parseFloat(input.getAttribute('data-price') || 0);
                // I-ignore ang value na 'none'
                if (addonValue !== 'none') {
                    addonTotal += addonPrice;
                }
            });

            const pricePerItemWithAddons = basePrice + addonTotal;
            const finalPrice = pricePerItemWithAddons * quantity;

            orderMenuBasePriceDisplay.textContent = formatPrice(basePrice);
            orderTotalPriceDisplay.textContent = formatPrice(finalPrice);
            orderFinalPriceInput.value = formatPrice(finalPrice); 
        }
        
        /** I-reset ang estado ng modal kapag binuksan. */
        function resetModalState() {
            orderQtyInput.value = 1;
            
            document.querySelectorAll('.addon-input').forEach(input => {
                // Ignore disabled (out of stock) inputs
                if (input.hasAttribute('disabled')) {
                    input.checked = false; // Tiyakin na walang out-of-stock ang naka-check
                    return; 
                }
                
                if (input.type === 'checkbox') {
                    input.checked = false;
                } else if (input.type === 'radio' && input.value === 'none') {
                    input.checked = true; // Default to 'None / Default'
                } else if (input.type === 'radio' && input.value !== 'none') {
                    input.checked = false;
                }
            });
            
            orderProofInput.value = '';

            setPaymentType('GCash'); // Default payment type
        }

        /** I-set ang payment type at i-update ang display ng forms. */
        function setPaymentType(type) {
            if (type === 'GCash') {
                orderPaymentTypeInput.value = 'GCash';
                gcashSection.style.display = 'block';
                proofSection.style.display = 'block';
                codNote.style.display = 'none';
                orderProofInput.required = true;
                gcashBtn.style.backgroundColor = '#388e3c';
                codBtn.style.backgroundColor = '#A0522D';
            } else if (type === 'COD') {
                orderPaymentTypeInput.value = 'COD';
                gcashSection.style.display = 'none';
                proofSection.style.display = 'none';
                codNote.style.display = 'block';
                orderProofInput.required = false;
                gcashBtn.style.backgroundColor = '#A0522D';
                codBtn.style.backgroundColor = '#388e3c';
            }
        }
        
        /** I-check kung kumpleto ang address/contact at buksan ang tamang modal. */
        function checkProfileAndOpenModal(data) {
             const hasCompleteProfile = !!data.address && !!data.contact_no;
             hasAddressAndContact = hasCompleteProfile;
             
             if (hasCompleteProfile) {
                 orderModal.style.display = 'flex';
             } else {
                 orderBlockPopup.style.display = 'flex';
             }
        }
        
        // --- NEW STAR RATING LOGIC ---
        function setRating(rating) {
            if (!ratingStarsContainer) return;
            // Tiyakin na ang rating ay valid number
            rating = parseInt(rating) || 0; 

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
                    const rating = parseInt(star.getAttribute('data-rating'));
                    setRating(rating);
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
                // Ibalik sa kasalukuyang rating
                const currentRating = parseInt(ratingStarsContainer.getAttribute('data-current-rating'));
                setRating(currentRating); 
            });
            
            setRating(0); // I-reset sa 0 sa umpisa
        }
        // --- END NEW STAR RATING LOGIC ---


        // --- Event Listeners ---

        // 1. Order Button Click Handler (Open Modal)
        orderButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const menuid = btn.getAttribute('data-menuid');
                const name = btn.getAttribute('data-name');
                const desc = btn.getAttribute('data-desc');
                const img = btn.getAttribute('data-img');
                const price = parseFloat(btn.getAttribute('data-price') || 0);

                orderMenuNameDisplay.textContent = name;
                orderMenuDescDisplay.textContent = desc;
                orderMenuImgDisplay.src = img;
                
                orderMenuIdInput.value = menuid;
                orderMenuInput.value = name;
                orderMenuBasePriceInput.value = price.toFixed(2); 

                resetModalState();
                updateTotalPrice();

                // I-check muna ang profile status bago ipakita ang order modal
                fetch('../Profile/profile_api.php')
                    .then(res => res.json())
                    .then(checkProfileAndOpenModal)
                    .catch(err => {
                        console.error('Error fetching profile:', err);
                        alert('Error checking profile. Please try again.');
                        orderModal.style.display = 'flex'; // Ipakita pa rin bilang fallback
                    });
            });
        });

        // 2. Quantity at Add-on Change Handler (Update Price)
        // I-handle ang input event para sa real-time updates habang nagta-type
        if (orderQtyInput) {
             orderQtyInput.addEventListener('input', updateTotalPrice);
             orderQtyInput.addEventListener('change', updateTotalPrice);
        }
        document.querySelectorAll('.addon-input').forEach(input => {
            input.addEventListener('change', updateTotalPrice);
        });

        // 3. Close Modal Handler (and others)
        if (closeBtn) closeBtn.addEventListener('click', function() { orderModal.style.display = 'none'; });
        if (orderModal) orderModal.addEventListener('click', function(e) { if (e.target === orderModal) { orderModal.style.display = 'none'; } });
        if (orderBlockPopup) orderBlockPopup.addEventListener('click', function(e) { if (e.target === orderBlockPopup) { orderBlockPopup.style.display = 'none'; } });
        if (goToProfileBtn) goToProfileBtn.onclick = function() { window.location.href = '../Profile/profile.php'; };
        if (oneTimeFeedbackModal) oneTimeFeedbackModal.addEventListener('click', function(e) { if (e.target === oneTimeFeedbackModal) { /* Gawin lang na kailangang i-click ang button */ } });

        // 4. Payment Type Handlers
        if(gcashBtn) gcashBtn.addEventListener('click', () => setPaymentType('GCash'));
        if(codBtn) codBtn.addEventListener('click', () => setPaymentType('COD'));

        // 5. Order Form Submission Handler (Gumamit ng AJAX)
        if (orderForm) {
            orderForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!hasAddressAndContact) {
                     orderModal.style.display = 'none';
                     orderBlockPopup.style.display = 'flex';
                     return;
                }
                
                // Simple validation for GCash proof
                if (orderPaymentTypeInput.value === 'GCash' && orderProofInput.required && !orderProofInput.files.length) {
                    alert("Please upload proof of payment for GCash.");
                    return;
                }

                const formData = new FormData(orderForm);
                
                // --- Logic para i-set ang final product name at i-delete ang 'none' values ---
                const selectedAddonsText = [];
                let basePrice = parseFloat(orderMenuBasePriceInput.value || 0);
                let addonTotal = 0;
                
                // Temporary array to track keys to delete
                const keysToDelete = []; 

                for (let [key, value] of formData.entries()) {
                    if (value === 'none') {
                        keysToDelete.push(key);
                    } else if (key.startsWith('addons[')) {
                        // Kung ito ay isang addon ID, hanapin ang pangalan at presyo
                        const input = orderForm.querySelector(`input[name='${key}'][value='${value}']`);
                        // CRITICAL CHECK: Tiyakin na HINDI disabled
                        if (input && !input.hasAttribute('disabled')) {
                            const label = input.closest('label');
                            // Use textContent for safety, remove price display
                            const text = label.textContent.trim().replace(/\s\(\+.+\)/, '').replace(/₱0\.00/, '').replace(/\(Out of Stock\)/i, '').trim(); 
                            selectedAddonsText.push(text);
                            addonTotal += parseFloat(input.getAttribute('data-price') || 0);
                        } else if (input && input.hasAttribute('disabled')) {
                             // Kung disabled, dapat itong i-delete sa formData para hindi maipasa
                             keysToDelete.push(key);
                        }
                    } else if (key === 'addons[]') {
                         // Para sa Checkboxes
                         const input = orderForm.querySelector(`input[name='addons[]'][value='${value}']`);
                         // CRITICAL CHECK: Tiyakin na HINDI disabled
                         if (input && !input.hasAttribute('disabled')) {
                            const label = input.closest('label');
                            // Use textContent for safety, remove price display
                            const text = label.textContent.trim().replace(/\s\(\+.+\)/, '').replace(/₱0\.00/, '').replace(/\(Out of Stock\)/i, '').trim();
                            selectedAddonsText.push(text);
                            addonTotal += parseFloat(input.getAttribute('data-price') || 0);
                        } else if (input && input.hasAttribute('disabled')) {
                            // Kung disabled, dapat itong i-delete sa formData para hindi maipasa
                            // Note: Para sa Checkboxes (multiple keys), mas kumplikado ang pag-delete, 
                            // pero dahil hindi ito naka-check kung disabled, hindi ito dapat mapasa.
                            // Para sa safety, kung sakali, ang server side validation ay dapat ding tiyakin na 
                            // walang disabled item ang maipasa.
                        }
                    }
                }

                // Delete 'none' values
                keysToDelete.forEach(key => formData.delete(key));
                
                // Re-evaluate the final price one last time based on the JS logic (for client-side security)
                // This is redundant since updateTotalPrice runs on change, but good for final submission check
                const finalPriceCheck = (basePrice + addonTotal) * parseInt(orderQtyInput.value || 1);
                orderFinalPriceInput.value = formatPrice(finalPriceCheck);
                formData.set('total_price', formatPrice(finalPriceCheck));

                // Finalize Menu Name
                let finalMenuName = orderMenuInput.value;
                if (selectedAddonsText.length > 0) {
                     finalMenuName += ' (' + selectedAddonsText.join(', ') + ')';
                }
                
                formData.set('menu', finalMenuName);
                
                // --- END Logic para i-set ang final product name ---

                // --- Server Request via Fetch API ---
                fetch(orderForm.action, {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    const contentType = res.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return res.json();
                    } else {
                        return res.text().then(text => { throw new Error('Non-JSON response: ' + text); });
                    }
                })
                .then(data => {
                    if (data.success) {
                        orderModal.style.display = 'none';
                        
                        // --- NEW LOGIC: I-check ang feedback status mula sa server ---
                        if (data.show_feedback_prompt && oneTimeFeedbackModal) {
                            oneTimeFeedbackModal.classList.add('show');
                            oneTimeFeedbackForm.reset(); 
                            setRating(0);
                        } else if (orderSuccessModal) {
                            orderSuccessModal.classList.add('show');
                        }
                        // ---------------------------------------------
                        
                    } else {
                        alert(data.message || 'Order failed. Please try again.');
                        console.error('Order failed details:', data);
                    }
                })
                .catch((err) => {
                    alert('Order failed: ' + err.message);
                    console.error('Order AJAX error:', err);
                });
            });
        }
        
        // 6. Feedback Form Submission Handler (CRITICAL)
        if (oneTimeFeedbackForm) {
            oneTimeFeedbackForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (parseInt(feedbackRatingInput.value) === 0) {
                    alert("Please select a star rating before submitting.");
                    return;
                }
                
                const feedbackData = new FormData(this);
                
                // I-submit ang form (CRITICAL FETCH CALL)
                fetch(oneTimeFeedbackForm.action, { // '../Feedback/save_feedback.php'
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
        
        // 7. Skip Feedback Button Handler
        if (skipFeedbackBtn) {
            skipFeedbackBtn.onclick = function() {
                setRating(0); 
                oneTimeFeedbackModal.classList.remove('show');
                orderSuccessModal.classList.add('show'); 
            };
        }
        
        // 8. Success Modal Handler
        if (orderSuccessOkBtn) {
            orderSuccessOkBtn.onclick = function() {
                window.location.href = '../Dashboard/dashboard.php'; 
            };
        }
    });
</script>
</body>
</html>