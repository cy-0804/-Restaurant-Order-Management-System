<?php
include 'config/db.php';
include 'includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 2rem;">Your Shopping Cart</h2>
</div>

<div class="glass-card" id="cart-content" style="display: none; max-width: 800px; margin: 0 auto;">
    <h3 style="font-size: 1.25rem; margin-bottom: 2rem; text-align: center;">Order Details</h3>
    
    <div class="cart-items" id="cart-items-container" style="margin-bottom: 2rem;">
    </div>
    
    <div style="padding-top: 1.5rem; margin-bottom: 2rem;">
        <div class="summary-row">
            <span>Subtotal</span>
            <span id="subtotal-val">RM 0.00</span>
        </div>
        <div class="summary-row">
            <span>Service Tax (6%)</span>
            <span id="tax-val">RM 0.00</span>
        </div>
        <div class="summary-row total">
            <span>Total</span>
            <span id="total-val">RM 0.00</span>
        </div>
    </div>
    
    <div style="display: flex; justify-content: flex-end; gap: 1rem;">
        <button class="btn btn-danger btn-sm" id="clear-cart-btn" style="background: transparent; border: 1px solid var(--border-color); color: var(--danger); box-shadow: none; border-radius: 9999px;">
            <i class="fa-solid fa-trash-can"></i> Clear
        </button>
        <div style="flex-grow: 1;"></div>
        <a href="menu.php" class="btn btn-secondary" style="border-radius: 9999px; padding: 0.5rem 1.5rem;">Add More Items</a>
        <a href="checkout.php" class="btn btn-primary" style="border-radius: 9999px; padding: 0.5rem 1.5rem;">Checkout</a>
    </div>
</div>

<div class="glass-card" id="empty-cart-state" style="max-width: 800px; margin: 0 auto; min-height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
    <i class="fa-solid fa-cart-arrow-down" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem;"></i>
    <h3>Your cart is empty</h3>
    <p style="color: var(--text-muted); margin-top: 0.5rem; margin-bottom: 2rem;">
        Add some delicious Sup Tulang or hot Mee Rebus to start your meal!
    </p>
    <a href="menu.php" class="btn btn-primary">
        Browse Menu <i class="fa-solid fa-arrow-right"></i>
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    renderCartPage();
    
    document.addEventListener('cartUpdated', renderCartPage);
    
    document.getElementById('clear-cart-btn').addEventListener('click', () => {
        if(confirm("Are you sure you want to empty your cart?")) {
            clearCart();
        }
    });
});

function renderCartPage() {
    const cartItems = getCart();
    const cartLayout = document.getElementById('cart-content');
    const emptyState = document.getElementById('empty-cart-state');
    const container = document.getElementById('cart-items-container');
    
    if (cartItems.length === 0) {
        cartLayout.style.display = 'none';
        emptyState.style.display = 'flex';
        return;
    }
    
    cartLayout.style.display = 'block';
    emptyState.style.display = 'none';
    
    container.innerHTML = '';
    
    let subtotal = 0;
    
    cartItems.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        const itemHtml = `
            <div class="cart-item" style="padding: 1rem 0; border-bottom: 1px dashed var(--border-color); justify-content: space-between;">
                <div class="cart-item-details">
                    <p style="font-weight: 500;">${item.name} <span style="color:var(--text-muted); font-size: 0.9rem;">(RM ${item.price.toFixed(2)})</span></p>
                </div>
                
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div class="quantity-controls">
                        <button class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                        <span class="qty-num">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                    
                    <button class="btn btn-sm" onclick="updateQty(${item.id}, -${item.quantity})" style="background: transparent; color: var(--text-muted); padding: 0; box-shadow: none;">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
    });
    
    const tax = subtotal * 0.06;
    const total = subtotal + tax;
    
    document.getElementById('subtotal-val').textContent = `RM ${subtotal.toFixed(2)}`;
    document.getElementById('tax-val').textContent = `RM ${tax.toFixed(2)}`;
    document.getElementById('total-val').textContent = `RM ${total.toFixed(2)}`;
}
</script>

<?php
include 'includes/footer.php';
?>
