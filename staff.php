<?php
require_once 'includes/auth.php';
check_role('staff');

include 'config/db.php';
include 'includes/header.php';
?>

<div style="margin-bottom: 2rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1.25rem;">
    <div>
        <h2 id="dashboard-title"><?php echo ucfirst($_SESSION['user_role'] ?? 'Staff'); ?> Dashboard - Orders Queue</h2>
        <p style="color:var(--text-muted);" id="dashboard-subtitle">Monitor, progress, and coordinate orders</p>
    </div>
    
    <div style="display:flex; gap:0.5rem; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); padding:0.25rem; border-radius:var(--radius-sm); align-items:center;">
        <button class="btn btn-primary btn-sm view-tab-btn" id="tab-btn-orders" onclick="switchDashboardView('orders')">
            <i class="fa-solid fa-list-check"></i> Orders Queue
        </button>
        <button class="btn btn-secondary btn-sm view-tab-btn" id="tab-btn-tables" onclick="switchDashboardView('tables')">
            <i class="fa-solid fa-table-cells"></i> Floor Map & Ordering
        </button>
        <div style="width: 1px; height: 20px; background: rgba(0,0,0,0.1); margin: 0 0.25rem;"></div>
        <a href="qr_generator.php" class="btn btn-secondary btn-sm" style="border-color:transparent;">
            <i class="fa-solid fa-qrcode"></i> Print Table QRs
        </a>
    </div>
</div>

<div id="view-orders-container" class="dashboard-grid">
    <aside class="stats-sidebar">
        <div class="glass-card stat-card">
            <div class="stat-value" id="stat-pending">0</div>
            <div class="stat-label">Pending Review</div>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-value" id="stat-preparing" style="color: var(--info);">0</div>
            <div class="stat-label">In Kitchen</div>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-value" id="stat-ready" style="color: var(--success);">0</div>
            <div class="stat-label">Ready for Pickup</div>
        </div>
    </aside>

    <section class="glass-card">
        <nav class="tab-nav" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
            <button class="tab-btn active" data-filter="all">All (<span id="tab-count-all">0</span>)</button>
            <button class="tab-btn" data-filter="pending">Pending (<span id="tab-count-pending">0</span>)</button>
            <button class="tab-btn" data-filter="preparing">In Progress (<span id="tab-count-preparing">0</span>)</button>
            <button class="tab-btn" data-filter="ready">Ready (<span id="tab-count-ready">0</span>)</button>
            <button class="tab-btn" data-filter="completed">Completed (<span id="tab-count-completed">0</span>)</button>
        </nav>
        
        <div class="orders-table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Type/Table</th>
                        <th>Customer Details</th>
                        <th>Items Summary</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                </tbody>
            </table>
        </div>
        
        <div id="empty-orders-state" style="display: none; text-align: center; padding: 4rem 2rem;">
            <i class="fa-solid fa-clipboard-check" style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h3>No orders found in this section</h3>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Orders placed by clients will show up here.</p>
        </div>
    </section>
</div>

<div id="view-tables-container" class="cart-layout" style="display: none; grid-template-columns: 1fr 340px;">
    <div class="glass-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:0.75rem;">
            <h3 style="font-size:1.15rem; margin-bottom:0;">
                Dining Room Floor Map
            </h3>
            <button class="btn btn-primary btn-sm" onclick="openScanner()">
                <i class="fa-solid fa-camera"></i> Scan Table QR
            </button>
        </div>
        
        <div class="table-selection-grid" id="waiter-table-grid">
        </div>
        
        <div style="display:flex; justify-content:center; gap:2rem; font-size:0.9rem; margin-top:2rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.05);">
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <div style="width:16px; height:16px; background:transparent; border:1px solid var(--border-color); border-radius:var(--radius-sm)"></div>
                <span>Available</span>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <div style="width:16px; height:16px; background:rgba(59, 130, 246, 0.15); border:1px solid rgba(59, 130, 246, 0.5); border-radius:var(--radius-sm)"></div>
                <span>Occupied / Dining</span>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <div style="width:16px; height:16px; background:var(--primary); border:1px solid var(--primary); border-radius:var(--radius-sm)"></div>
                <span>Selected Table</span>
            </div>
        </div>
    </div>
    
    <div class="glass-card" id="table-sidebar" style="height:fit-content; position:sticky; top:5.5rem;">
        <h3 style="font-size:1.25rem; margin-bottom:1rem; color:var(--primary);" id="sidebar-table-title">Select a Table</h3>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;" id="sidebar-table-desc">Click on any table in the floor map to load options.</p>
        
        <div id="table-actions" style="display:none;">
            <div style="margin-bottom:1.5rem; padding:1rem; border:1px solid rgba(255,255,255,0.05); border-radius:var(--radius-sm); background:rgba(10,10,12,0.3);" id="sidebar-order-details">
            </div>
            
            <button class="btn btn-primary btn-full" id="sidebar-btn-place-order" style="margin-bottom:0.75rem;">
                Place New Order <i class="fa-solid fa-plus"></i>
            </button>
            
            <button class="btn btn-secondary btn-full" id="sidebar-btn-track" style="display:none;">
                Track Active Order <i class="fa-solid fa-map-location-dot"></i>
            </button>
        </div>
    </div>
</div>

<audio id="notif-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-84.wav" preload="auto"></audio>

<div id="receipt-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:1000; align-items:center; justify-content:center; padding:2rem;">
    <div class="glass-card" style="max-width:500px; width:100%; position:relative; max-height:90vh; overflow-y:auto;">
        <button onclick="closeReceiptModal()" style="position:absolute; top:1.25rem; right:1.25rem; background:transparent; border:none; color:var(--text-main); font-size:1.5rem; cursor:pointer;">&times;</button>
        <h3 style="margin-bottom:1.5rem;" id="modal-title">Payment Receipt Review</h3>
        
        <div style="text-align:center; margin-bottom:1.5rem;" id="modal-img-container">
        </div>
        
        <div id="modal-order-details" style="font-size:0.95rem; line-height:1.6; color:var(--text-muted); margin-bottom:1.5rem; border-top:1px solid rgba(255,255,255,0.05); padding-top:1rem;">
        </div>
        
        <div style="display:flex; gap:1rem;">
            <button class="btn btn-primary" id="modal-verify-btn" style="flex:1;">
                Verify Payment <i class="fa-solid fa-check"></i>
            </button>
            <button class="btn btn-secondary" onclick="closeReceiptModal()">Close</button>
        </div>
    </div>
</div>

<div id="qr-scanner-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:1001; align-items:center; justify-content:center; padding:2rem;">
    <div class="glass-card" style="max-width:480px; width:100%; position:relative; text-align:center;">
        <button onclick="closeScanner()" style="position:absolute; top:1.25rem; right:1.25rem; background:transparent; border:none; color:var(--text-main); font-size:1.5rem; cursor:pointer;">&times;</button>
        <h3 style="margin-bottom:1.5rem;"><i class="fa-solid fa-qrcode" style="color:var(--primary)"></i> Scan Table QR Code</h3>
        
        <div id="qr-reader" style="width:100%; max-width:350px; margin:0 auto; background:#18181f; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden;"></div>
        
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:1.5rem; line-height:1.5;">
            Point your mobile camera at the table QR Code card.
        </p>
        
        <button class="btn btn-secondary btn-full" onclick="closeScanner()" style="margin-top:1.5rem;">Cancel</button>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
let activeDashboardView = 'orders';
let activeFilter = 'all';
let ordersList = [];
let dbConnectedGlobal = false;
let selectedTable = null;
let activeOrdersList = [];

function switchDashboardView(view) {
    activeDashboardView = view;
    
    document.getElementById('view-orders-container').style.display = (view === 'orders') ? 'grid' : 'none';
    document.getElementById('view-tables-container').style.display = (view === 'tables') ? 'grid' : 'none';
    
    const btnOrders = document.getElementById('tab-btn-orders');
    const btnTables = document.getElementById('tab-btn-tables');
    
    if (view === 'orders') {
        btnOrders.className = "btn btn-primary btn-sm view-tab-btn";
        btnTables.className = "btn btn-secondary btn-sm view-tab-btn";
        document.getElementById('dashboard-title').textContent = "<?php echo ucfirst($_SESSION['user_role'] ?? 'Staff'); ?> Dashboard - Orders Queue";
        document.getElementById('dashboard-subtitle').textContent = "Monitor, progress, and coordinate orders";
    } else {
        btnOrders.className = "btn btn-secondary btn-sm view-tab-btn";
        btnTables.className = "btn btn-primary btn-sm view-tab-btn";
        document.getElementById('dashboard-title').textContent = "<?php echo ucfirst($_SESSION['user_role'] ?? 'Staff'); ?> Dashboard - Floor Map";
        document.getElementById('dashboard-subtitle').textContent = "Select a table or scan QR to place and monitor table orders";
        
        renderTables();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    dbConnectedGlobal = <?php echo $db_connected ? 'true' : 'false'; ?>;
    
    fetchDashboardData();
    
    setInterval(fetchDashboardData, 5000);
    
    const tabBtns = document.querySelectorAll('.tab-nav .tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.getAttribute('data-filter');
            renderDashboard();
        });
    });
});

async function fetchDashboardData() {
    if (dbConnectedGlobal) {
        try {
            const res = await fetch('api.php?action=get_all_orders');
            const orders = await res.json();
            
            if (ordersList.length > 0 && orders.length > ordersList.length) {
                playNotificationSound();
                showToast("New order received!");
            }
            
            ordersList = orders;
            activeOrdersList = orders.filter(o => o.order_type === 'walk-in' && o.order_status !== 'completed' && o.order_status !== 'cancelled');
            
            renderDashboard();
            if (activeDashboardView === 'tables') {
                renderTables();
            }
        } catch (e) {
            console.error("Dashboard poll error: ", e);
        }
    } else {
        const orders = getMockOrders();
        
        if (ordersList.length > 0 && orders.length > ordersList.length) {
            playNotificationSound();
            showToast("New table/online order received!");
        }
        
        ordersList = orders;
        activeOrdersList = orders.filter(o => o.order_type === 'walk-in' && o.order_status !== 'completed' && o.order_status !== 'cancelled');
        
        renderDashboard();
        if (activeDashboardView === 'tables') {
            renderTables();
        }
    }
}


function renderDashboard() {
    const pendingCount = ordersList.filter(o => o.order_status === 'pending').length;
    const preparingCount = ordersList.filter(o => o.order_status === 'preparing').length;
    const readyCount = ordersList.filter(o => o.order_status === 'ready').length;
    const completedCount = ordersList.filter(o => o.order_status === 'completed').length;
    
    document.getElementById('stat-pending').textContent = pendingCount;
    document.getElementById('stat-preparing').textContent = preparingCount;
    document.getElementById('stat-ready').textContent = readyCount;
    
    document.getElementById('tab-count-all').textContent = ordersList.length;
    document.getElementById('tab-count-pending').textContent = pendingCount;
    document.getElementById('tab-count-preparing').textContent = preparingCount;
    document.getElementById('tab-count-ready').textContent = readyCount;
    document.getElementById('tab-count-completed').textContent = completedCount;
    
    let filteredOrders = ordersList;
    if (activeFilter !== 'all') {
        filteredOrders = ordersList.filter(o => o.order_status === activeFilter);
    }
    
    const tbody = document.getElementById('orders-tbody');
    const emptyState = document.getElementById('empty-orders-state');
    
    tbody.innerHTML = '';
    
    if (filteredOrders.length === 0) {
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    
    filteredOrders.forEach(order => {
        const formattedTime = new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        let itemsText = '';
        if (order.items && order.items.length > 0) {
            itemsText = order.items.map(i => `${i.quantity}x ${i.name}`).join('<br>');
        }
        
        let actionHtml = '';
        if (order.order_status === 'pending') {
            actionHtml = `<button class="btn btn-primary btn-sm" onclick="updateOrderStatus(${order.id}, 'preparing')">Accept Order</button>`;
        } else if (order.order_status === 'preparing') {
            actionHtml = `<button class="btn btn-primary btn-sm" style="background-color: var(--info);" onclick="updateOrderStatus(${order.id}, 'ready')">Mark Ready</button>`;
        } else if (order.order_status === 'ready') {
            actionHtml = `<button class="btn btn-primary btn-sm" style="background-color: var(--success);" onclick="updateOrderStatus(${order.id}, 'completed')">Complete</button>`;
        }
        
        actionHtml += `<button class="btn btn-secondary btn-sm" onclick="printKitchenSlip(${order.id})" style="margin-left:0.5rem; padding:0.4rem 0.6rem;" title="Print Kitchen Slip"><i class="fa-solid fa-print"></i></button>`;
        
        let paymentCol = '';
        if (order.order_type === 'online') {
            if (order.payment_status === 'verified') {
                paymentCol = `<span class="status-badge ready" style="font-size:0.75rem; padding:0.1rem 0.5rem;">Verified</span>`;
            } else {
                paymentCol = `
                    <button class="btn btn-secondary btn-sm" onclick="viewReceipt(${order.id})" style="padding:0.25rem 0.5rem; font-size:0.75rem; border-color:var(--border-color); color:var(--primary);">
                        Verify Receipt
                    </button>
                `;
            }
        } else {
            paymentCol = `<span class="status-badge completed" style="font-size:0.75rem; padding:0.1rem 0.5rem; border:1px solid rgba(255,255,255,0.05)">Cash</span>`;
        }
        
        const trHtml = `
            <tr>
                <td style="font-weight:600;">
                    #${order.id}<br>
                    <span style="font-size:0.75rem; color:var(--text-muted); font-weight:400;">${formattedTime}</span>
                </td>
                <td>
                    <span style="font-weight:600; font-size:0.95rem; text-transform:uppercase;">
                        ${order.order_type}
                    </span><br>
                    <span style="font-size:0.85rem; color:var(--primary); font-weight:600;">
                        ${order.table_number ? 'Table ' + order.table_number : (order.delivery_address ? 'Delivery' : 'Pickup')}
                    </span>
                </td>
                <td style="font-size:0.9rem;">
                    <strong>${order.customer_name}</strong><br>
                    <span style="color:var(--text-muted); font-size:0.8rem;">${order.customer_email}</span>
                    ${order.delivery_address ? `<br><span style="color:var(--text-muted); font-size:0.8rem; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden;" title="${order.delivery_address}">${order.delivery_address}</span>` : ''}
                </td>
                <td style="font-size:0.85rem; line-height:1.4; color:var(--text-muted);">
                    ${itemsText}
                </td>
                <td style="font-weight:600; color:var(--primary);">
                    RM ${parseFloat(order.total_amount).toFixed(2)}
                </td>
                <td>${paymentCol}</td>
                <td>
                    <span class="status-badge ${order.order_status}" style="font-size:0.75rem;">${order.order_status === 'preparing' ? 'In Progress' : order.order_status}</span>
                </td>
                <td style="white-space:nowrap;">
                    ${actionHtml}
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', trHtml);
    });
}

window.updateOrderStatus = async function(orderId, newStatus) {
    if (dbConnectedGlobal) {
        try {
            const res = await fetch(`api.php?action=update_status&id=${orderId}&status=${newStatus}`);
            const data = await res.json();
            if (data.success) {
                showToast(`Order #${orderId} status updated to ${newStatus}`);
                fetchDashboardData();
            }
        } catch (e) {
            console.error(e);
        }
    } else {
        const success = updateMockOrderStatus(orderId, newStatus);
        if (success) {
            showToast(`Mock Order #${orderId} updated to ${newStatus}`);
            fetchDashboardData();
        }
    }
};

window.viewReceipt = function(orderId) {
    const order = ordersList.find(o => o.id == orderId);
    if (order) {
        document.getElementById('modal-title').textContent = `Order #${order.id} Payment Receipt`;
        
        const modalImgContainer = document.getElementById('modal-img-container');
        if (order.payment_receipt && order.payment_receipt.startsWith('data:image')) {
            modalImgContainer.innerHTML = `<img src="${order.payment_receipt}" style="max-width:100%; max-height:300px; border-radius:var(--radius-sm);" alt="Bank receipt">`;
        } else if (order.payment_receipt) {
            modalImgContainer.innerHTML = `<div style="padding:2rem; background:rgba(255,255,255,0.02); border:1px dashed var(--border-color); border-radius:var(--radius-sm); color:var(--primary); font-weight:600;"><i class="fa-solid fa-file-pdf" style="font-size:2.5rem; margin-bottom:0.5rem; display:block;"></i> ${order.payment_receipt}</div>`;
        } else {
            modalImgContainer.innerHTML = `<div style="padding:2rem; background:rgba(255,255,255,0.02); border:1px dashed var(--danger); border-radius:var(--radius-sm); color:var(--danger); font-weight:600;"><i class="fa-solid fa-triangle-exclamation" style="font-size:2.5rem; margin-bottom:0.5rem; display:block;"></i> No receipt uploaded</div>`;
        }
        
        document.getElementById('modal-order-details').innerHTML = `
            Customer: <strong>${order.customer_name}</strong><br>
            Amount Due: <strong style="color:var(--primary)">RM ${parseFloat(order.total_amount).toFixed(2)}</strong><br>
            E-Mail: <strong>${order.customer_email}</strong>
        `;
        
        const verifyBtn = document.getElementById('modal-verify-btn');
        verifyBtn.onclick = async () => {
            if (dbConnectedGlobal) {
                try {
                    const res = await fetch(`api.php?action=verify_payment&id=${order.id}`);
                    const data = await res.json();
                    if(data.success) {
                        showToast(`Payment verified for order #${order.id}`);
                        closeReceiptModal();
                        fetchDashboardData();
                    }
                } catch(e) {}
            } else {
                const orders = getMockOrders();
                const mockOrder = orders.find(o => o.id == order.id);
                if (mockOrder) {
                    mockOrder.payment_status = 'verified';
                    saveMockOrders(orders);
                    showToast(`Mock payment verified for order #${order.id}`);
                    closeReceiptModal();
                    fetchDashboardData();
                }
            }
        };
        
        document.getElementById('receipt-modal').style.display = 'flex';
    }
};

window.closeReceiptModal = function() {
    document.getElementById('receipt-modal').style.display = 'none';
};

window.printKitchenSlip = function(orderId) {
    const order = ordersList.find(o => o.id == orderId);
    if (!order) return;
    
    const formattedTime = new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    let itemsLi = order.items.map(item => `
        <li style="font-size: 1.4rem; padding: 0.5rem 0; border-bottom: 1px dashed #000; display:flex; justify-content:space-between;">
            <strong>${item.quantity} x ${item.name}</strong>
            <span>[  ]</span>
        </li>
    `).join('');
    
    const printWindow = window.open('', '_blank', 'width=600,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Kitchen Slip - Order #${order.id}</title>
            <style>
                body { font-family: 'Courier New', monospace; color: #000; background: #fff; padding: 20px; }
                .header { text-align: center; border-bottom: 2px double #000; padding-bottom: 10px; margin-bottom: 15px; }
                ul { list-style: none; padding: 0; margin: 0; }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            <div class="header">
                <h2 style="margin:0;">KITCHEN TICKET</h2>
                <h3 style="margin:5px 0;">ORDER #${order.id}</h3>
                <p style="margin:2px 0;">Type: ${order.order_type.toUpperCase()} | ${order.table_number ? 'TABLE ' + order.table_number : (order.delivery_address ? 'DELIVERY' : 'PICKUP')}</p>
                <p style="margin:2px 0;">Time: ${formattedTime}</p>
            </div>
            <ul>
                ${itemsLi}
            </ul>
            <div style="margin-top:20px; text-align:center; font-size:0.9rem;">
                * END OF SLIP *
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
};


function renderTables() {
    const grid = document.getElementById('waiter-table-grid');
    grid.innerHTML = '';
    
    for (let t = 1; t <= 8; t++) {
        const linkedOrder = activeOrdersList.find(o => o.table_number == t);
        
        let statusClass = '';
        let orderText = '';
        
        if (linkedOrder) {
            statusClass = 'occupied';
            orderText = `<span style="font-size:0.75rem; font-weight:400; opacity:0.8; display:block; margin-top:0.25rem;">${linkedOrder.order_status === 'preparing' ? 'IN PROGRESS' : linkedOrder.order_status.toUpperCase()}</span>`;
        }
        
        if (selectedTable === t) {
            statusClass = 'selected';
        }
        
        const cardHtml = `
            <div class="table-card ${statusClass}" data-table="${t}">
                <span>T - ${t}</span>
                ${orderText}
            </div>
        `;
        grid.insertAdjacentHTML('beforeend', cardHtml);
    }
    
    document.querySelectorAll('.table-card').forEach(card => {
        card.addEventListener('click', () => {
            const tableNum = parseInt(card.getAttribute('data-table'));
            selectTable(tableNum);
        });
    });
    
    if (selectedTable) {
        updateSidebar();
    }
}

function selectTable(tableNum) {
    selectedTable = tableNum;
    renderTables();
    updateSidebar();
}

function updateSidebar() {
    const title = document.getElementById('sidebar-table-title');
    const desc = document.getElementById('sidebar-table-desc');
    const actions = document.getElementById('table-actions');
    const orderDetails = document.getElementById('sidebar-order-details');
    const btnPlaceOrder = document.getElementById('sidebar-btn-place-order');
    const btnTrack = document.getElementById('sidebar-btn-track');
    
    title.textContent = `Table ${selectedTable}`;
    desc.style.display = 'none';
    actions.style.display = 'block';
    
    const activeOrder = activeOrdersList.find(o => o.table_number == selectedTable);
    
    if (activeOrder) {
        orderDetails.style.display = 'block';
        orderDetails.innerHTML = `
            <div style="font-weight:600; margin-bottom:0.5rem; color:var(--primary);">Active Order Info</div>
            <div style="font-size:0.9rem; margin-bottom:0.25rem;">Order: <strong>#${activeOrder.id}</strong></div>
            <div style="font-size:0.9rem; margin-bottom:0.25rem;">Status: <strong class="status-badge ${activeOrder.order_status}" style="font-size:0.75rem; padding:0 0.5rem;">${activeOrder.order_status === 'preparing' ? 'In Progress' : activeOrder.order_status}</strong></div>
            <div style="font-size:0.9rem;">Total: <strong>RM ${parseFloat(activeOrder.total_amount).toFixed(2)}</strong></div>
        `;
        
        btnPlaceOrder.textContent = "Add/Modify Order";
        btnTrack.style.display = 'block';
        btnTrack.onclick = () => {
            const url = dbConnectedGlobal ? `track.php?id=${activeOrder.id}` : `track.php?mock_id=${activeOrder.id}`;
            window.location.href = url;
        };
    } else {
        orderDetails.style.display = 'none';
        btnPlaceOrder.textContent = "Place New Order";
        btnTrack.style.display = 'none';
    }
    
    btnPlaceOrder.onclick = () => {
        window.location.href = `menu.php?table=${selectedTable}`;
    };
}

let html5QrcodeScanner = null;

window.openScanner = function() {
    document.getElementById('qr-scanner-modal').style.display = 'flex';
    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    const config = { fps: 10, qrbox: { width: 220, height: 220 } };
    
    html5QrcodeScanner.start(
        { facingMode: "environment" }, 
        config,
        qrCodeSuccessCallback,
        qrCodeErrorCallback
    ).catch(err => {
        console.error("Camera access failed", err);
        alert("Camera access failed: Make sure you give camera permissions or select a table card manually instead.");
        closeScanner();
    });
};

function qrCodeSuccessCallback(decodedText, decodedResult) {
    try {
        const url = new URL(decodedText);
        const tableNum = url.searchParams.get("table");
        
        if (tableNum) {
            showToast("Table QR detected! Loading...");
            closeScanner();
            window.location.href = `menu.php?table=${tableNum}`;
        } else {
            alert("No table parameter found in the QR URL.");
        }
    } catch(e) {
        alert("Could not read QR Code. Make sure you scan the official table QR code.");
    }
}

function qrCodeErrorCallback(errorMessage) {}

window.closeScanner = function() {
    document.getElementById('qr-scanner-modal').style.display = 'none';
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().catch(err => {
            console.warn("Scanner stop error: ", err);
        });
    }
};


window.triggerNotificationSim = function() {
    const audio = document.getElementById('notif-sound');
    audio.currentTime = 0;
    audio.play().catch(e => {
        alert("Playing sound notification simulation!");
    });
};

function playNotificationSound() {
    const audio = document.getElementById('notif-sound');
    audio.currentTime = 0;
    audio.play().catch(e => {
        console.warn("Sound blocked by browser: " + e.message);
    });
}

window.clearAllOrdersSim = function() {
    if (confirm("Are you sure you want to reset all mock orders in local storage?")) {
        localStorage.removeItem('mock_orders');
        showToast("Mock orders cleared!");
        fetchDashboardData();
    }
};
</script>

<?php
include 'includes/footer.php';
?>
