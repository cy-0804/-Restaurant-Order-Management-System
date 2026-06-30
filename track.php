<?php
include 'config/db.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$mock_id = isset($_GET['mock_id']) ? htmlspecialchars($_GET['mock_id']) : null;

include 'includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2>Order Tracking</h2>
            <p style="color:var(--text-muted);" id="tracking-title-order">Order #Z-849201</p>
            <span class="status-badge pending" id="overall-status-badge" style="display:inline-block; margin-top:0.35rem;">pending</span>
        </div>
    </div>
    
    <div id="sim-info-banner" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 2rem; display: none;">
        <p style="font-size: 0.9rem; line-height: 1.5; color: var(--text-muted);">
            <strong style="color:var(--info);"><i class="fa-solid fa-circle-info"></i> Simulation Mode:</strong> 
            Open the <a href="staff.php" target="_blank" style="color:var(--primary); text-decoration:underline; font-weight:600;">Staff Dashboard</a> in a new tab. Change this order's status there, and this page will update instantly!
        </p>
    </div>

    <div class="glass-card" style="padding: 3rem 2rem; margin-bottom: 2rem;">
        <h3 style="text-align: center; margin-bottom: 3rem; font-size: 1.25rem;">Track Your Order</h3>
        
        <div class="tracking-stepper" style="margin-bottom: 4rem;">
            <div class="step-progress-bar" id="stepper-progress-line"></div>
            
            <div class="step-node" id="step-pending">
                <div class="step-circle"><i class="fa-solid fa-receipt"></i></div>
                <div class="step-label">Pending</div>
            </div>
            
            <div class="step-node" id="step-preparing">
                <div class="step-circle"><i class="fa-solid fa-kitchen-set"></i></div>
                <div class="step-label">In Progress</div>
            </div>
            
            <div class="step-node" id="step-ready">
                <div class="step-circle"><i class="fa-solid fa-bell-concierge"></i></div>
                <div class="step-label">Ready</div>
            </div>
            
            <div class="step-node" id="step-completed">
                <div class="step-circle"><i class="fa-solid fa-circle-check"></i></div>
                <div class="step-label">Completed</div>
            </div>
        </div>
        
        <div>
            <span style="font-weight: 600; font-size: 1rem;" id="info-est-label">Estimated Delivery/Pickup Time</span><br>
            <span style="color:var(--text-muted); font-size: 0.95rem; display:inline-block; margin-top:0.25rem;" id="info-est-time">25 mins</span>
        </div>
    </div>
    
    <div class="glass-card" style="padding: 3rem 2rem;">
        <h3 style="text-align: center; font-size: 1.25rem; margin-bottom: 2rem;">
            Order Summary
        </h3>
        
        <div id="track-items-summary" style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1.5rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.95rem;">
                <span style="color:var(--text-muted);">2x Sup Tulang (Original)</span>
                <span>RM 36.00</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.95rem;">
                <span style="color:var(--text-muted);">1x Mee Rebus Tulang</span>
                <span>RM 20.00</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.95rem;">
                <span style="color:var(--text-muted);">1x Teh Tarik</span>
                <span>RM 3.50</span>
            </div>
        </div>
        
        <div style="border-top: 1px dashed var(--border-color); padding-top: 1rem; margin-bottom: 1rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.95rem; margin-bottom:0.5rem; color:var(--text-muted);">
                <span>Subtotal</span>
                <span id="track-subtotal">RM 56.13</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.95rem; margin-bottom:0.5rem; color:var(--text-muted);">
                <span>Service Tax (6%)</span>
                <span id="track-tax">RM 3.37</span>
            </div>
        </div>
        
        <div style="border-top: 1px solid rgba(0,0,0,0.1); margin-bottom: 1.5rem;"></div>
        
        <div style="display:flex; justify-content:space-between; font-size:1.15rem; margin-bottom:0.75rem; font-weight:700;">
            <span>Payment Method</span>
            <span style="color:var(--text-muted); font-weight:400;" id="info-payment-method">Online Banking</span>
        </div>
        
        <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1.15rem;">
            <span>Grand Total</span>
            <span style="color:var(--primary)" id="track-total">RM 59.50</span>
        </div>
        
        <div id="reupload-receipt-section" style="display:none; margin-top: 2rem; border-top: 1px dashed var(--danger); padding-top: 1.5rem;">
            <h4 style="color: var(--danger); margin-bottom: 0.5rem;"><i class="fa-solid fa-triangle-exclamation"></i> Payment Receipt Invalid</h4>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Your previous payment receipt was rejected. Please re-upload a valid receipt to proceed with your order.</p>
            <form id="reupload-form">
                <input type="hidden" name="id" id="reupload-order-id">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <input type="file" class="form-control" name="payment_receipt" accept="image/*,.pdf" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm btn-full" style="background-color: var(--danger); border-color: var(--danger);">Re-Upload Receipt</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const orderId = "<?php echo $order_id; ?>";
    const mockId = "<?php echo $mock_id; ?>";
    const dbConnected = <?php echo $db_connected ? 'true' : 'false'; ?>;
    
    if (!dbConnected) {
        document.getElementById('sim-info-banner').style.display = 'block';
    }

    pollOrderStatus();
    setInterval(pollOrderStatus, 5000);
    
    async function pollOrderStatus() {
        if (dbConnected && orderId) {
            try {
                const res = await fetch(`api.php?action=get_order_status&id=${orderId}`);
                if (!res.ok) throw new Error("HTTP error");
                const order = await res.json();
                if (order.success === false) throw new Error(order.error || "Order not found");
                bindOrderDetails(order);
            } catch (e) {
                console.error("Error polling order status: ", e);
                document.getElementById('tracking-title-order').textContent = "Order not found";
            }
        } else if (mockId) {
            const orders = getMockOrders();
            const order = orders.find(o => o.id == mockId);
            if (order) {
                bindOrderDetails(order);
            }
        }
    }
    
    function bindOrderDetails(order) {
        const status = order.order_status;
        const type = order.order_type;
        const isWalkIn = (type === 'walk-in');
        
        const badge = document.getElementById('overall-status-badge');
        if (badge) {
            badge.className = `status-badge ${status}`;
            badge.textContent = status === 'preparing' ? 'In Progress' : status;
        }

        document.getElementById('tracking-title-order').textContent = `Order #${order.id}`;
        
        const isDelivery = order.delivery_method === 'delivery' || localStorage.getItem('delivery_method') === 'delivery';
        
        document.getElementById('info-est-label').textContent = isDelivery ? 'Estimated Delivery Time' : 'Estimated Pickup Time';
        
        const methodElem = document.getElementById('info-payment-method');
        const reuploadSection = document.getElementById('reupload-receipt-section');
        if (order.payment_status === 'verified') {
            methodElem.textContent = 'Paid Online';
            methodElem.style.color = 'var(--text-muted)';
            if (reuploadSection) reuploadSection.style.display = 'none';
        } else if (order.payment_status === 'failed') {
            methodElem.textContent = 'Payment Failed / Rejected';
            methodElem.style.color = 'var(--danger)';
            if (reuploadSection) {
                reuploadSection.style.display = 'block';
                document.getElementById('reupload-order-id').value = order.id;
            }
        } else {
            methodElem.textContent = 'Pay at Counter / Pending';
            methodElem.style.color = 'var(--text-muted)';
            if (reuploadSection) reuploadSection.style.display = 'none';
        }
        
        document.getElementById('track-total').textContent = `RM ${parseFloat(order.total_amount).toFixed(2)}`;
        
        const container = document.getElementById('track-items-summary');
        container.innerHTML = '';
        let subtotal = 0;
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
                subtotal += itemTotal;
                const row = `
                    <div style="display:flex; justify-content:space-between; font-size:0.95rem;">
                        <span style="color:var(--text-muted);">${item.quantity}x ${item.name}</span>
                        <span>RM ${itemTotal.toFixed(2)}</span>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', row);
            });
        }

        const total = parseFloat(order.total_amount);
        const tax = Math.max(total - subtotal, 0);
        document.getElementById('track-subtotal').textContent = `RM ${subtotal.toFixed(2)}`;
        document.getElementById('track-tax').textContent = `RM ${tax.toFixed(2)}`;
        
        const nodes = {
            'pending': document.getElementById('step-pending'),
            'preparing': document.getElementById('step-preparing'),
            'ready': document.getElementById('step-ready'),
            'completed': document.getElementById('step-completed')
        };
        
        Object.values(nodes).forEach(node => {
            node.classList.remove('active', 'completed');
        });
        
        const line = document.getElementById('stepper-progress-line');
        
        if (status === 'pending') {
            nodes.pending.classList.add('active');
            line.style.width = '0%';
            document.getElementById('info-est-time').textContent = "Pending setup";
        } else if (status === 'preparing') {
            nodes.pending.classList.add('completed');
            nodes.preparing.classList.add('active');
            line.style.width = '25%';
            document.getElementById('info-est-time').textContent = "10 - 15 mins";
        } else if (status === 'ready') {
            nodes.pending.classList.add('completed');
            nodes.preparing.classList.add('completed');
            nodes.ready.classList.add('active');
            line.style.width = '50%';
            document.getElementById('info-est-time').textContent = isDelivery ? "Arriving now" : "Ready";
        } else if (status === 'completed') {
            nodes.pending.classList.add('completed');
            nodes.preparing.classList.add('completed');
            nodes.ready.classList.add('completed');
            nodes.completed.classList.add('active');
            line.style.width = '75%';
            document.getElementById('info-est-time').textContent = isDelivery ? "Delivered" : (isWalkIn ? "Dined" : "Picked Up");
        } else if (status === 'cancelled') {
            document.getElementById('info-est-time').textContent = "-";
        }
    }
    
    const reuploadForm = document.getElementById('reupload-form');
    if (reuploadForm) {
        reuploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!dbConnected) {
                alert("Cannot upload receipt in simulation mode.");
                return;
            }
            const formData = new FormData(reuploadForm);
            try {
                const res = await fetch('api.php?action=reupload_receipt', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    alert("Receipt successfully re-uploaded! Status changed to Pending.");
                    reuploadForm.reset();
                    pollOrderStatus();
                } else {
                    alert(data.error || "Failed to upload receipt.");
                }
            } catch (err) {
                alert("An error occurred during upload.");
            }
        });
    }
});
</script>

<?php
include 'includes/footer.php';
?>
