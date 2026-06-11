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
                <span>RM 56.13</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.95rem; margin-bottom:0.5rem; color:var(--text-muted);">
                <span>Service Tax (6%)</span>
                <span>RM 3.37</span>
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
    
    
    
    async function pollOrderStatus() {
        if (dbConnected && orderId) {
            try {
                const res = await fetch(`api.php?action=get_order_status&id=${orderId}`);
                if (!res.ok) throw new Error("HTTP error");
                const order = await res.json();
                bindOrderDetails(order);
            } catch (e) {
                console.error("Error polling order status: ", e);
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
        badge.className = `status-badge ${status}`;
        badge.textContent = status;
        
        const isDelivery = order.delivery_method === 'delivery' || localStorage.getItem('delivery_method') === 'delivery';
        
        document.getElementById('info-est-label').textContent = isDelivery ? 'Estimated Delivery Time' : 'Estimated Pickup Time';
        document.getElementById('info-payment-method').textContent = order.payment_status === 'verified' ? 'Paid Online' : 'Pay at Counter / Pending';
        document.getElementById('track-total').textContent = `RM ${parseFloat(order.total_amount).toFixed(2)}`;
        
        const container = document.getElementById('track-items-summary');
        container.innerHTML = '';
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                const row = `
                    <div style="display:flex; justify-content:space-between; font-size:0.95rem;">
                        <span style="color:var(--text-muted);">${item.quantity}x ${item.name}</span>
                        <span>RM ${(item.price * item.quantity).toFixed(2)}</span>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', row);
            });
        }
        
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
            line.style.width = '33.3%';
            document.getElementById('info-est-time').textContent = "10 - 15 mins";
        } else if (status === 'ready') {
            nodes.pending.classList.add('completed');
            nodes.preparing.classList.add('completed');
            nodes.ready.classList.add('active');
            line.style.width = '66.6%';
            document.getElementById('info-est-time').textContent = isDelivery ? "Arriving now" : "Ready";
        } else if (status === 'completed') {
            nodes.pending.classList.add('completed');
            nodes.preparing.classList.add('completed');
            nodes.ready.classList.add('completed');
            nodes.completed.classList.add('active');
            line.style.width = '100%';
            document.getElementById('info-est-time').textContent = isDelivery ? "Delivered" : (isWalkIn ? "Dined" : "Picked Up");
        } else if (status === 'cancelled') {
            document.getElementById('info-est-time').textContent = "-";
        }
    }
});
</script>

<?php
include 'includes/footer.php';
?>
