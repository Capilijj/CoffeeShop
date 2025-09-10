<?php
require_once '../LoginPage/database_connection.php';
$menu_items = [];
// Kinuha ko ang 'menuid' na column para ma-access ito sa front-end
$sql = "SELECT menuid, menuname, description, price, imageurl FROM menu ORDER BY menuid ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
?>
<link rel="stylesheet" href="../Order/order.css">
<link rel="stylesheet" href="../LoginPage/core.css">
<link rel="stylesheet" href="../Order/order-success.css">
<link rel="stylesheet" href="./menu.css">


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
        <div class="order-success-title">Order placed successfully!</div>
        <button id="orderSuccessOkBtn" class="order-success-btn">Back to Dashboard</button>
    </div>
</div>
<div id="orderModal">
    <div class="order-modal-content">
        <button id="orderCloseBtn">&times;</button>
        <img id="orderMenuImg" class="order-menu-img" src="" alt="Menu Image">
        <div class="order-menu-title" id="orderMenuName">Menu Name</div>
        <div class="order-menu-desc" id="orderMenuDesc">Menu Description</div>
        <div class="order-menu-price">Price: <span id="orderMenuPrice">₱25</span></div>
    <form id="orderForm" action="/Order/save_order.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="menuid" id="orderMenuIdInput" value="">
            <input type="hidden" name="menu" id="orderMenuInput" value="">
            <input type="hidden" name="desc" id="orderMenuDescInput" value="">
            <input type="hidden" name="price" id="orderMenuPriceInput" value="25">
            <input type="hidden" name="payment_type" id="orderPaymentType" value="GCash">
            <div style="display:flex;justify-content:center;align-items:center;margin:12px 0 18px 0;">
                <label for="orderQty" style="margin-right:10px;font-weight:600;color:#A0522D;">Quantity:</label>
                <input type="number" name="quantity" id="orderQty" value="1" min="1" max="99" style="width:60px;text-align:center;font-size:1.1rem;padding:4px 0;border-radius:6px;border:1px solid #A0522D;">
            </div>
            <div id="paymentOptions" style="margin-bottom: 12px;">
                <button type="button" id="gcashBtn" class="order-submit-btn" style="background:#388e3c;margin-right:8px;">GCash</button>
                <button type="button" id="codBtn" class="order-submit-btn" style="background:#A0522D;">Cash on Delivery</button>
            </div>
            <div class="order-gcash" id="gcashSection">
                <label>Pay via GCash:</label><br>
                <img src="../Image/QR.jpg" alt="GCash QR Code" style="width:260px;height:260px;object-fit:contain;border-radius:16px;box-shadow:0 2px 12px rgba(160,82,45,0.10);margin:12px 0;">
            </div>
            <div class="order-upload" id="proofSection">
                <label for="orderProof">Upload Proof of Payment:</label>
                <input type="file" name="proof" id="orderProof" accept="image/*" required>
            </div>
            <div id="codNote" style="display:none;color:#388e3c;font-weight:600;margin-bottom:10px;">You selected Cash on Delivery. Please prepare payment upon delivery.</div>
            <button type="submit" class="order-submit-btn">Place Order</button>
        </form>
    </div>
</div>
<style>
.order-block-popup {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.45);
    display: flex; align-items: center; justify-content: center;
    z-index: 99999;
}
.order-block-content {
    background: #fff;
    padding: 32px 28px 22px 28px;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.18);
    text-align: center;
    max-width: 350px;
    width: 95vw;
}
.order-block-content h2 {
    color: #A0522D;
    margin-bottom: 12px;
}
.order-block-content p {
    color: #333;
    margin-bottom: 18px;
}
.order-block-content button {
    background: #A0522D;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 6px;
    font-size: 1.08rem;
    font-weight: 600;
    cursor: pointer;
}
</style>
<div id="orderBlockPopup" class="order-block-popup" style="display:none;">
    <div class="order-block-content">
        <h2>Complete Your Profile</h2>
        <p>You must provide your address and contact number before placing an order.</p>
        <button id="goToProfileBtn">Go to Profile</button>
    </div>
</div>
<script src="../Order/order.js"></script>
<script>
// Payment option toggle logic and AJAX order submit with success modal
document.addEventListener('DOMContentLoaded', function() {
        // Dynamically set menu modal values from button
        document.querySelectorAll('.order-menu-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                        // Check user profile before showing order modal
                        fetch('/Profile/profile_api.php')
                            .then(res => res.json())
                            .then(user => {
                                if (!user.address || !user.contact_no) {
                                    document.getElementById('orderBlockPopup').style.display = 'flex';
                                } else {
                                    document.getElementById('orderMenuName').textContent = btn.getAttribute('data-name');
                                    document.getElementById('orderMenuDesc').textContent = btn.getAttribute('data-desc');
                                    document.getElementById('orderMenuImg').src = btn.getAttribute('data-img');
                                    document.getElementById('orderMenuPrice').textContent = '₱' + parseFloat(btn.getAttribute('data-price')).toFixed(2);
                                    document.getElementById('orderMenuInput').value = btn.getAttribute('data-name');
                                    document.getElementById('orderMenuDescInput').value = btn.getAttribute('data-desc');
                                    document.getElementById('orderMenuIdInput').value = btn.getAttribute('data-menuid');
                                    document.getElementById('orderMenuPriceInput').value = btn.getAttribute('data-price');
                                    document.getElementById('orderModal').style.display = 'flex';
                                }
                            })
                            .catch(() => {
                                alert('Could not verify profile. Please log in again.');
                            });
                });
        });

        document.getElementById('goToProfileBtn').onclick = function() {
            window.location.href = '/Profile/profile.php';
        };
        // ...existing code for payment option toggle and AJAX order submit...
        const gcashBtn = document.getElementById('gcashBtn');
        const codBtn = document.getElementById('codBtn');
        const gcashSection = document.getElementById('gcashSection');
        const proofSection = document.getElementById('proofSection');
        const codNote = document.getElementById('codNote');
        const proofInput = document.getElementById('orderProof');
        const orderForm = document.getElementById('orderForm');
        const paymentTypeInput = document.getElementById('orderPaymentType');
        const orderModal = document.getElementById('orderModal');
        const orderSuccessModal = document.getElementById('orderSuccessModal');
        const orderSuccessOkBtn = document.getElementById('orderSuccessOkBtn');

        function setGCash() {
                gcashSection.style.display = '';
                proofSection.style.display = '';
                codNote.style.display = 'none';
                proofInput.required = true;
                gcashBtn.style.background = '#388e3c';
                codBtn.style.background = '#A0522D';
                paymentTypeInput.value = 'GCash';
        }
        function setCOD() {
                gcashSection.style.display = 'none';
                proofSection.style.display = 'none';
                codNote.style.display = '';
                proofInput.required = false;
                codBtn.style.background = '#388e3c';
                gcashBtn.style.background = '#A0522D';
                paymentTypeInput.value = 'Cash on Delivery';
        }
        gcashBtn.addEventListener('click', setGCash);
        codBtn.addEventListener('click', setCOD);
        if (window.orderModal) {
                window.orderModal.addEventListener('show', setGCash);
        }
        // AJAX order submit
        if (orderForm) {
                orderForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        // Debug: log all form values before submit
                        const debugForm = {};
                        Array.from(orderForm.elements).forEach(el => {
                                if (el.name) debugForm[el.name] = el.value;
                        });
                        console.log('Order form values:', debugForm);
                        if (codNote.style.display !== 'none') {
                                proofInput.value = '';
                        }
                        const formData = new FormData(orderForm);
                        fetch(orderForm.action, {
                                method: 'POST',
                                body: formData
                        })
                        .then(async res => {
                                // Debug: log status and response
                                console.log('Order AJAX status:', res.status);
                                let text = await res.text();
                                console.log('Order AJAX response:', text);
                                try {
                                        const data = JSON.parse(text);
                                        if (data.success) {
                                                orderModal.style.display = 'none';
                                                orderSuccessModal.classList.add('show');
                                        } else {
                                                alert(data.message || 'Order failed. Please try again.');
                                                console.error('Order failed details:', data);
                                        }
                                } catch (err) {
                                        alert('Order failed: Invalid server response.');
                                        console.error('Order AJAX parse error:', err);
                                }
                        })
                        .catch((err) => {
                                alert('Order failed.');
                                console.error('Order AJAX error:', err);
                        });
                });
        }
        if (orderSuccessOkBtn) {
                orderSuccessOkBtn.onclick = function() {
                        window.location.href = '/Dashboard/dashboard.php';
                };
        }
        setGCash();
});
</script>