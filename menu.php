<?php
include 'config/db.php';

$categories = [];
$menu_items = [];

if ($db_connected) {
    try {
        $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
        $menu_items = $pdo->query("SELECT m.*, c.name as category_name FROM menu_items m JOIN categories c ON m.category_id = c.id WHERE m.is_available = 1")->fetchAll();
    } catch (Exception $e) {
        $db_connected = false;
    }
}

if (!$db_connected) {
    $categories = [
        ['id' => 1, 'name' => 'Soups', 'icon' => 'soup'],
        ['id' => 2, 'name' => 'Noodles', 'icon' => 'noodles'],
        ['id' => 3, 'name' => 'Rice Dishes', 'icon' => 'rice'],
        ['id' => 4, 'name' => 'Drinks', 'icon' => 'drinks']
    ];
    $menu_items = [
        ['id' => 1, 'category_id' => 1, 'name' => 'Sup Tulang (Original)', 'description' => 'Signature mutton bone marrow soup cooked with rich spices. Served with straws to suck out the delicious marrow.', 'price' => 18.00, 'category_name' => 'Soups'],
        ['id' => 2, 'category_id' => 1, 'name' => 'Sup Daging', 'description' => 'Aromatic beef soup loaded with tender beef chunks, potatoes, carrots, and topped with crispy fried shallots.', 'price' => 12.00, 'category_name' => 'Soups'],
        ['id' => 3, 'category_id' => 2, 'name' => 'Mee Rebus Tulang', 'description' => 'Famous thick and savory sweet potato gravy served with yellow noodles, mutton bone marrow, boiled egg, and lime.', 'price' => 15.00, 'category_name' => 'Noodles'],
        ['id' => 4, 'category_id' => 2, 'name' => 'Mee Goreng Mamak', 'description' => 'Spicy wok-fried yellow noodles with tofu, potato cubes, fritters, beansprouts, and beef slices.', 'price' => 9.00, 'category_name' => 'Noodles'],
        ['id' => 5, 'category_id' => 3, 'name' => 'Nasi Goreng Kampung', 'description' => 'Traditional Malay fried rice stir-fried with crispy anchovies, water spinach (kangkung), and hot bird\'s eye chilies.', 'price' => 8.50, 'category_name' => 'Rice Dishes'],
        ['id' => 6, 'category_id' => 4, 'name' => 'Teh Tarik', 'description' => 'Hot, frothy pulled black tea sweet milk beverage. Malaysia\'s national drink.', 'price' => 3.00, 'category_name' => 'Drinks'],
        ['id' => 7, 'category_id' => 4, 'name' => 'Sirap Bandung', 'description' => 'Refreshing rose syrup beverage mixed with sweet condensed milk and served over ice.', 'price' => 3.50, 'category_name' => 'Drinks'],
        ['id' => 8, 'category_id' => 4, 'name' => 'Kopi O', 'description' => 'Classic hot strong black coffee served sweet without milk.', 'price' => 2.50, 'category_name' => 'Drinks']
    ];
}

include 'includes/header.php';
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="margin-bottom: 0.25rem;">Our Legendary Menu</h2>
        <p style="color: var(--text-muted);">Select from our authentic freshly cooked dishes</p>
    </div>
    
    <div style="position: relative; width: 300px; max-width: 100%;">
        <input type="text" id="menu-search" class="form-control" placeholder="Search menu..." style="padding-left: 2.5rem;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
    </div>
</div>

<div class="menu-layout">
    <aside class="filter-sidebar glass-card" style="padding: 1.25rem;">
        <h3 style="font-size: 1.15rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
            Categories
        </h3>
        <ul class="filter-list">
            <li>
                <button class="filter-btn active" data-category="all">
                    <i class="fa-solid fa-list-ul"></i> All Items
                </button>
            </li>
            <?php foreach ($categories as $cat): ?>
                <li>
                    <button class="filter-btn" data-category="<?php echo htmlspecialchars($cat['name']); ?>">
                        <?php 
                        $icon = 'fa-utensils';
                        if ($cat['name'] === 'Soups') $icon = 'fa-bowl-food';
                        elseif ($cat['name'] === 'Noodles') $icon = 'fa-plate-wheat';
                        elseif ($cat['name'] === 'Rice Dishes') $icon = 'fa-bowl-rice';
                        elseif ($cat['name'] === 'Drinks') $icon = 'fa-mug-hot';
                        ?>
                        <i class="fa-solid <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($cat['name']); ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <section>
        <div class="menu-grid" id="menu-container">
            <?php foreach ($menu_items as $item): ?>
                <div class="glass-card menu-card" data-category="<?php echo htmlspecialchars($item['category_name']); ?>" data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>">
                    <div class="menu-img-container">
                        <?php 
                        $img_src = 'images/food/sup_tulang_1781199026957.png'; // default
                        if ($item['id'] == 1) $img_src = 'images/food/sup_tulang_1781199026957.png';
                        elseif ($item['id'] == 2) $img_src = 'images/food/sup_daging_1781199039170.png';
                        elseif ($item['id'] == 3) $img_src = 'images/food/mee_rebus_1781199050612.png';
                        elseif ($item['id'] == 4) $img_src = 'images/food/mee_goreng_1781199072142.png';
                        elseif ($item['id'] == 5) $img_src = 'images/food/nasi_goreng_1781199082484.png';
                        elseif ($item['id'] == 6) $img_src = 'images/food/teh_tarik_1781199092193.png';
                        elseif ($item['id'] == 7) $img_src = 'images/food/sirap_bandung_1781199114123.png';
                        elseif ($item['id'] == 8) $img_src = 'images/food/kopi_o_1781199124202.png';
                        ?>
                        <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <span class="menu-badge"><?php echo htmlspecialchars($item['category_name']); ?></span>
                    </div>
                    
                    <div class="menu-info">
                        <div>
                            <h4 class="menu-item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="menu-item-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                        <div class="menu-footer">
                            <span class="menu-price">RM <?php echo number_format($item['price'], 2); ?></span>
                            <button class="btn btn-primary btn-sm add-to-cart-btn" 
                                    data-id="<?php echo $item['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($item['name']); ?>" 
                                    data-price="<?php echo $item['price']; ?>">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div id="no-results" style="display: none; text-align: center; padding: 4rem 2rem;">
            <i class="fa-solid fa-circle-question" style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h3>No menu items found</h3>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Try adjusting your keywords or filters</p>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('menu-search');
    const filterBtns = document.querySelectorAll('.filter-sidebar .filter-btn');
    const menuCards = document.querySelectorAll('#menu-container .menu-card');
    const noResults = document.getElementById('no-results');
    
    let activeCategory = 'all';
    let searchQuery = '';

    searchInput.addEventListener('input', (e) => {
        searchQuery = e.target.value.toLowerCase().trim();
        filterMenu();
    });

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeCategory = btn.getAttribute('data-category');
            filterMenu();
        });
    });

    function filterMenu() {
        let visibleCount = 0;
        menuCards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            const cardName = card.getAttribute('data-name');
            
            const categoryMatch = (activeCategory === 'all' || cardCategory === activeCategory);
            const searchMatch = (searchQuery === '' || cardName.includes(searchQuery));
            
            if (categoryMatch && searchMatch) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = parseInt(btn.getAttribute('data-id'));
            const name = btn.getAttribute('data-name');
            const price = parseFloat(btn.getAttribute('data-price'));
            
            addToCart(id, name, price, 1);
        });
    });
});
</script>

<?php
include 'includes/footer.php';
?>
