<?php
include 'config/db.php';
include 'includes/header.php';

$table_number = isset($_SESSION['table_number']) ? $_SESSION['table_number'] : null;
$order_type = isset($_SESSION['order_type']) ? $_SESSION['order_type'] : 'online';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 2rem;">Checkout</h2>
    
    <div class="cart-layout" style="grid-template-columns: 1fr 320px;">
        <div class="glass-card">
            <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                Customer Details
            </h3>
            
            <form id="checkout-form" method="POST" action="api.php?action=create_order" enctype="multipart/form-data">
                <input type="hidden" name="order_type" id="order_type" value="<?php echo $order_type; ?>">
                
                <?php if ($order_type === 'walk-in' && $table_number): ?>
                    <div class="form-group">
                        <label for="table_number_display">Table Number</label>
                        <input type="text" id="table_number_display" class="form-control" value="Table <?php echo htmlspecialchars($table_number); ?>" disabled>
                        <input type="hidden" name="table_number" id="table_number" value="<?php echo $table_number; ?>">
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="customer_name">Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="customer_email">Email Address <span style="color:var(--danger)">*</span></label>
                    <input type="email" name="customer_email" id="customer_email" class="form-control" placeholder="name@example.com" required>
                    <small style="color:var(--text-muted); font-size:0.8rem; margin-top:0.25rem; display:block;">
                        For sending order confirmation details.
                    </small>
                </div>
                
                <?php if ($order_type !== 'walk-in'): ?>
                <div class="form-group" id="delivery-address-group" style="display: none;">
                    <label for="delivery_address">Delivery Address <span style="color:var(--danger)">*</span></label>
                    <textarea name="delivery_address" id="delivery_address" class="form-control" rows="3" placeholder="Enter your full shipping address"></textarea>
                </div>
                
                <div class="form-group" id="pickup-address-group" style="display: none;">
                    <label>Pickup Location</label>
                    <div style="padding: 0.75rem 1rem; background: #faf8f5; border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-muted);">
                        <strong>Sup Tulang ZZ</strong><br>
                        123 Food Street, Culinary District<br>
                        Johor Bahru, 80000
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control">
                        <?php if ($order_type === 'walk-in'): ?>
                            <option value="cash">Pay Cash at Counter</option>
                        <?php endif; ?>
                        <option value="ewallet">e-Wallet (Gateway)</option>
                        <option value="card">Credit / Debit Card (Gateway)</option>
                        <option value="manual_transfer">Manual Bank Transfer (Upload Receipt)</option>
                    </select>
                </div>
                
                <div id="manual-receipt-group" style="display: none; border-top: 1px dashed var(--border-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                    <div style="background: rgba(245, 158, 11, 0.05); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.95rem; margin-bottom: 0.5rem; color: var(--primary);">Bank Transfer Instructions</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">Please transfer the exact amount to:</p>
                        <ul style="list-style: none; padding: 0; font-size: 0.9rem; font-weight: 600;">
                            <li>Bank: Maybank</li>
                            <li>Account Name: Sup Tulang ZZ</li>
                            <li>Account No: 1234 5678 9012</li>
                        </ul>
                    </div>
                    
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.95rem;">Upload Payment Receipt <span style="color:var(--danger)">*</span></label>
                    <label class="receipt-upload-box" id="upload-box-label">
                        <input type="file" name="payment_receipt" id="payment_receipt" accept="image/*,.pdf" style="display: none;">
                        <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                        <p style="font-weight: 500; margin-bottom: 0.25rem;">Click to upload receipt</p>
                        <p style="font-size: 0.8rem; color: var(--text-muted);">PNG, JPG, or PDF up to 5MB</p>
                        <img id="receipt-preview" class="preview-image" alt="Receipt Preview">
                    </label>
                </div>

                <div id="payment-receipt-group" style="display: none; border-top: 1px dashed var(--border-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                    <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); border-radius: var(--radius-sm); padding: 1.25rem; text-align: center; font-weight: 600; margin-bottom: 1.5rem;">
                        <i class="fa-solid fa-lock"></i> Secure Payment: You will be redirected to the payment gateway after clicking Place Order.
                        <span id="gateway-ref-text" style="display:none;"></span>
                    </div>
                </div>
                
                <input type="hidden" name="cart_data" id="cart_data_input">
                <input type="hidden" name="total_amount" id="total_amount_input">
                <input type="hidden" name="payment_gateway_paid" id="payment_gateway_paid" value="0">
                
                <button type="submit" class="btn btn-primary btn-full" style="margin-top: 1rem;">
                    Place Order
                </button>
            </form>
        </div>
        
        <div class="glass-card" style="height: fit-content; position: sticky; top: 5.5rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                Your Order
            </h3>
            <div id="checkout-items-list" style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1rem; max-height:200px; overflow-y:auto;">
            </div>
            
            <div style="border-top: 1px dashed var(--border-color); padding-top: 1rem; margin-bottom: 1rem;">
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:0.5rem; color:var(--text-muted);">
                    <span>Subtotal</span>
                    <span id="checkout-subtotal-val">RM 0.00</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:0.5rem; color:var(--text-muted);">
                    <span>Service Tax (6%)</span>
                    <span id="checkout-tax-val">RM 0.00</span>
                </div>
            </div>
            
            <div class="summary-row" style="margin-top:0.5rem; padding-top:0.5rem;">
                <span>Total Amount:</span>
                <strong style="color:var(--primary); font-size:1.2rem;" id="checkout-total-val">RM 0.00</strong>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cartItems = getCart();
    
    if (cartItems.length === 0) {
        alert("Your cart is empty. Redirecting to menu.");
        window.location.href = "menu.php";
        return;
    }
    
    const deliveryMethod = localStorage.getItem('delivery_method') || 'delivery';
    const deliveryGroup = document.getElementById('delivery-address-group');
    const pickupGroup = document.getElementById('pickup-address-group');
    const deliveryInput = document.getElementById('delivery_address');
    
    if (deliveryGroup && pickupGroup && deliveryInput) {
        if (deliveryMethod === 'pickup') {
            pickupGroup.style.display = 'block';
            deliveryGroup.style.display = 'none';
            deliveryInput.removeAttribute('required');
        } else {
            pickupGroup.style.display = 'none';
            deliveryGroup.style.display = 'block';
            deliveryInput.setAttribute('required', 'required');
        }
    }

    
    const container = document.getElementById('checkout-items-list');
    let subtotal = 0;
    
    cartItems.forEach(item => {
        subtotal += item.price * item.quantity;
        const itemHtml = `
            <div style="display:flex; justify-content:space-between; font-size:0.95rem;">
                <span style="color:var(--text-muted);">${item.quantity}x ${item.name}</span>
                <span>RM ${(item.price * item.quantity).toFixed(2)}</span>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
    });
    
    const tax = subtotal * 0.06;
    const total = subtotal + tax;
    
    document.getElementById('checkout-subtotal-val').textContent = `RM ${subtotal.toFixed(2)}`;
    document.getElementById('checkout-tax-val').textContent = `RM ${tax.toFixed(2)}`;
    document.getElementById('checkout-total-val').textContent = `RM ${total.toFixed(2)}`;
    document.getElementById('total_amount_input').value = total.toFixed(2);
    document.getElementById('cart_data_input').value = JSON.stringify(cartItems);

    const paymentMethodSelect = document.getElementById('payment_method');
    const paymentReceiptGroup = document.getElementById('payment-receipt-group');
    const manualReceiptGroup = document.getElementById('manual-receipt-group');
    const paymentReceiptInput = document.getElementById('payment_receipt');
    
    function togglePaymentGroup() {
        const method = paymentMethodSelect.value;
        if (method === 'ewallet' || method === 'card') {
            paymentReceiptGroup.style.display = 'block';
            manualReceiptGroup.style.display = 'none';
        } else if (method === 'manual_transfer') {
            paymentReceiptGroup.style.display = 'none';
            manualReceiptGroup.style.display = 'block';
        } else {
            paymentReceiptGroup.style.display = 'none';
            manualReceiptGroup.style.display = 'none';
        }
    }
    
    paymentMethodSelect.addEventListener('change', togglePaymentGroup);
    togglePaymentGroup();

    if (paymentReceiptInput) {
        paymentReceiptInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('receipt-preview');
            const icon = document.querySelector('.upload-icon');
            const texts = document.querySelectorAll('#upload-box-label p');
            
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if(icon) icon.style.display = 'none';
                    texts.forEach(t => t.style.display = 'none');
                }
                reader.readAsDataURL(file);
            } else if (file && file.type === 'application/pdf') {
                preview.style.display = 'none';
                if(icon) {
                    icon.className = 'fa-solid fa-file-pdf upload-icon';
                    icon.style.color = '#ef4444';
                    icon.style.display = 'block';
                }
                if(texts.length > 0) {
                    texts[0].textContent = file.name;
                    texts[0].style.display = 'block';
                }
                if(texts.length > 1) texts[1].style.display = 'none';
            }
        });
    }

    const gatewayModal = document.getElementById('gateway-modal');
    const gatewayAmountLabel = document.getElementById('gateway-amount-label');
    const gatewayPaidInput = document.getElementById('payment_gateway_paid');
    
    window.closeGatewayModal = function() {
        gatewayModal.style.display = 'none';
    };
    
    window.simulateGatewaySuccess = function() {
        const ref = 'SP-' + Math.floor(10000000 + Math.random() * 90000000);
        
        gatewayPaidInput.value = '1';
        
        const refSpan = document.getElementById('gateway-ref-text');
        if (refSpan) refSpan.textContent = ref;
        
        closeGatewayModal();
        showToast("Mock payment processed successfully! Finalizing order...");
        
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            const submitEvent = new Event('submit', { cancelable: true, bubbles: true });
            form.dispatchEvent(submitEvent);
        }
    };
    
    const form = document.getElementById('checkout-form');
    form.addEventListener('submit', function(e) {
        const method = document.getElementById('payment_method') ? document.getElementById('payment_method').value : null;
        const receiptInput = document.getElementById('payment_receipt');
        const isPaidGateway = gatewayPaidInput.value === '1';
        
        if (method === 'manual_transfer' && receiptInput && (!receiptInput.files || receiptInput.files.length === 0)) {
            e.preventDefault();
            
            if (typeof showToast === 'function') {
                showToast("Please upload your bank transfer receipt before placing the order.");
            } else {
                alert("Please upload your bank transfer receipt before placing the order.");
            }
            
            const uploadBox = document.getElementById('upload-box-label');
            if (uploadBox) {
                uploadBox.style.transition = 'all 0.3s ease';
                uploadBox.style.borderColor = 'var(--danger)';
                uploadBox.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
                uploadBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                setTimeout(() => {
                    uploadBox.style.borderColor = 'var(--border-color)';
                    uploadBox.style.backgroundColor = '#faf8f5';
                }, 3000);
            }
            return;
        }

        if ((method === 'ewallet' || method === 'card') && !isPaidGateway) {
            e.preventDefault();
            
            gatewayAmountLabel.textContent = `RM ${total.toFixed(2)}`;
            const optionsDiv = document.getElementById('gateway-dynamic-options');
            const warningDiv = document.getElementById('gateway-dynamic-warning');
            
            if (method === 'ewallet') {
                optionsDiv.innerHTML = `
                    <label for="gateway_provider">Select e-Wallet Provider</label>
                    <select id="gateway_provider" class="form-control">
                        <option value="tng">Touch 'n Go eWallet</option>
                        <option value="grab">GrabPay</option>
                        <option value="boost">Boost</option>
                        <option value="shopee">ShopeePay</option>
                    </select>
                `;
                warningDiv.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color:var(--primary)"></i> This is a simulated e-Wallet transaction. Clicking pay will complete your order checkout instantly.`;
            } else if (method === 'card') {
                optionsDiv.innerHTML = `
                    <label>Card Details</label>
                    <input type="text" class="form-control" placeholder="0000 0000 0000 0000" style="margin-bottom:0.5rem;" value="4111 1111 1111 1111" readonly>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="text" class="form-control" placeholder="MM/YY" value="12/28" readonly>
                        <input type="text" class="form-control" placeholder="CVV" value="123" readonly>
                    </div>
                `;
                warningDiv.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color:var(--primary)"></i> This is a simulated Credit/Debit Card transaction. Clicking pay will complete your order checkout instantly.`;
            }
            
            gatewayModal.style.display = 'flex';
            return;
        }

        if (!isDbConnected) {
            e.preventDefault();
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const method = document.getElementById('payment_method').value;
            const orderDetails = {
                order_type: document.getElementById('order_type').value,
                table_number: document.getElementById('table_number') ? parseInt(document.getElementById('table_number').value) : null,
                customer_name: document.getElementById('customer_name').value,
                customer_email: document.getElementById('customer_email').value,
                delivery_method: deliveryMethod,
                delivery_address: deliveryMethod === 'delivery' ? (document.getElementById('delivery_address') ? document.getElementById('delivery_address').value : null) : null,
                total_amount: total.toFixed(2),
                payment_receipt: method === 'manual_transfer' ? 'receipt_uploaded.jpg' : (isPaidGateway ? 'SUP-PAY Gateway Ref: ' + document.getElementById('gateway-ref-text').textContent : null),
                payment_status: method === 'manual_transfer' ? 'pending' : (isPaidGateway ? 'verified' : 'pending'),
                items: cartItems
            };
            
            const mockOrder = createMockOrder(orderDetails);
            clearCart();
            
            window.location.href = `order_confirmation.php?mock_id=${mockOrder.id}`;
        }
    });
});
</script>

<div id="gateway-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:1002; align-items:center; justify-content:center; padding:2rem;">
    <div class="glass-card" style="max-width:480px; width:100%; position:relative; padding:2.5rem 2rem;">
        <button onclick="closeGatewayModal()" style="position:absolute; top:1.25rem; right:1.25rem; background:transparent; border:none; color:var(--text-main); font-size:1.5rem; cursor:pointer;">&times;</button>
        
        <div style="text-align:center; margin-bottom:2rem; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:1rem;">
            <div style="display:inline-flex; align-items:center; gap:0.5rem; font-size:1.5rem; font-weight:700; color:#4f46e5;">
                <i class="fa-solid fa-bolt" style="color:var(--primary)"></i> SUP-PAY <span style="font-size:1rem; color:var(--text-muted); font-weight:400;">Gateway</span>
            </div>
            <p style="color:var(--text-muted); font-size:0.8rem; margin-top:0.25rem;">SECURED PAYMENT EMULATOR</p>
        </div>
        
        <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:var(--radius-sm); padding:1rem; margin-bottom:1.5rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:0.5rem;">
                <span style="color:var(--text-muted)">Merchant:</span>
                <strong>Sup Tulang ZZ</strong>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.9rem; align-items:center;">
                <span style="color:var(--text-muted)">Amount Due:</span>
                <strong style="color:var(--primary); font-size:1.15rem;" id="gateway-amount-label">RM 0.00</strong>
            </div>
        </div>
        
        <div class="form-group" id="gateway-dynamic-options">
        </div>
        
        <div id="gateway-dynamic-warning" style="background:rgba(245,158,11,0.05); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:0.75rem 1rem; margin-bottom:2rem; font-size:0.85rem; color:var(--text-muted); line-height:1.5; text-align:left;">
            <i class="fa-solid fa-triangle-exclamation" style="color:var(--primary)"></i> This is a simulated transaction. Clicking pay will register a mock paid callback state to complete your order checkout instantly.
        </div>
        
        <div style="display:flex; gap:1rem;">
            <button class="btn btn-primary" onclick="simulateGatewaySuccess()" style="flex:1; background:linear-gradient(135deg, #4f46e5, #06b6d4); color:#fff; box-shadow:0 4px 14px rgba(79, 70, 229, 0.4);">
                Simulate Payment
            </button>
            <button class="btn btn-secondary" onclick="closeGatewayModal()">Cancel</button>
        </div>
    </div>
</div>
</script>

<?php
include 'includes/footer.php';
?>
