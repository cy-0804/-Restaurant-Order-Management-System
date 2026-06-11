
let cart = JSON.parse(localStorage.getItem('restaurant_cart')) || [];
let isDbConnected = false;

document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
    checkDatabaseConnection();
    setupEventListeners();
});

async function checkDatabaseConnection() {
    try {
        const res = await fetch('config/db.php?check_db=1');
        const data = await res.json();
        isDbConnected = data.connected;
        if (!isDbConnected) {
            console.warn("MySQL Database not connected. Running in client-side Mock Mode. Error: " + data.error);
        } else {
            console.log("MySQL Database connected successfully. Running in Database Mode.");
        }
    } catch (e) {
        isDbConnected = false;
        console.warn("Backend server not responding. Running in client-side Mock Mode (localStorage fallback).");
    }
}

function setupEventListeners() {
    const receiptInput = document.getElementById('payment_receipt');
    if (receiptInput) {
        receiptInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                const preview = document.getElementById('receipt_preview');
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }
}


function getCart() {
    return cart;
}

function saveCart() {
    localStorage.setItem('restaurant_cart', JSON.stringify(cart));
    updateCartBadge();
}

function addToCart(id, name, price, quantity = 1) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantity += quantity;
    } else {
        cart.push({ id, name, price: parseFloat(price), quantity });
    }
    saveCart();
    showToast(`Added ${name} to cart!`);
}

function updateQty(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        saveCart();
        document.dispatchEvent(new CustomEvent('cartUpdated'));
    }
}

function clearCart() {
    cart = [];
    saveCart();
    document.dispatchEvent(new CustomEvent('cartUpdated'));
}

function updateCartBadge() {
    const badges = document.querySelectorAll('.cart-badge');
    const totalCount = cart.reduce((total, item) => total + item.quantity, 0);
    badges.forEach(badge => {
        badge.textContent = totalCount;
        badge.style.display = totalCount > 0 ? 'inline-block' : 'none';
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `<span style="color:var(--primary)">🔔</span> <span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}


const mockMenuItems = [
    { id: 1, category_id: 1, name: 'Sup Tulang (Original)', description: 'Signature mutton bone marrow soup cooked with rich spices. Served with straws to suck out the marrow.', price: 18.00 },
    { id: 2, category_id: 1, name: 'Sup Daging', description: 'Aromatic beef soup loaded with tender beef chunks, potatoes, carrots, and crispy fried shallots.', price: 12.00 },
    { id: 3, category_id: 2, name: 'Mee Rebus Tulang', description: 'Famous thick sweet potato gravy with yellow noodles, mutton bone marrow, boiled egg, and green chili.', price: 15.00 },
    { id: 4, category_id: 2, name: 'Mee Goreng Mamak', description: 'Spicy stir-fried yellow noodles with tofu, potato cubes, fritters, beansprouts, and beef slices.', price: 9.00 },
    { id: 5, category_id: 3, name: 'Nasi Goreng Kampung', description: 'Traditional Malay fried rice with crispy anchovies, water spinach, and hot bird\'s eye chilies.', price: 8.50 },
    { id: 6, category_id: 4, name: 'Teh Tarik', description: 'Hot, frothy pulled black tea sweet milk beverage. Malaysia\'s national drink.', price: 3.00 },
    { id: 7, category_id: 4, name: 'Sirap Bandung', description: 'Refreshing rose syrup beverage mixed with sweet condensed milk and served over ice.', price: 3.50 },
    { id: 8, category_id: 4, name: 'Kopi O', description: 'Classic hot strong black coffee served sweet without milk.', price: 2.50 }
];

function getMockOrders() {
    return JSON.parse(localStorage.getItem('mock_orders')) || [];
}

function saveMockOrders(orders) {
    localStorage.setItem('mock_orders', JSON.stringify(orders));
}

function createMockOrder(orderDetails) {
    const orders = getMockOrders();
    const newOrder = {
        id: orders.length + 1001, // Start mock IDs from 1001
        order_type: orderDetails.order_type,
        table_number: orderDetails.table_number || null,
        customer_name: orderDetails.customer_name,
        customer_email: orderDetails.customer_email || '',
        delivery_address: orderDetails.delivery_address || null,
        total_amount: parseFloat(orderDetails.total_amount),
        payment_status: orderDetails.order_type === 'online' ? 'pending' : 'verified',
        payment_receipt: orderDetails.payment_receipt || null,
        order_status: 'pending',
        items: orderDetails.items,
        created_at: new Date().toISOString()
    };
    orders.push(newOrder);
    saveMockOrders(orders);
    return newOrder;
}

function updateMockOrderStatus(orderId, newStatus) {
    const orders = getMockOrders();
    const order = orders.find(o => o.id == orderId);
    if (order) {
        order.order_status = newStatus;
        if (newStatus === 'completed' || newStatus === 'ready') {
            order.payment_status = 'verified'; // Auto verify on completion/ready
        }
        saveMockOrders(orders);
        return true;
    }
    return false;
}
