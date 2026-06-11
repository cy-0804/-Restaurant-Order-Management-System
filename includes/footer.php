<?php
$current_page = basename($_SERVER['PHP_SELF']);
$show_full_footer = in_array($current_page, ['index.php', 'menu.php']);
?>
    </main>
    
    <footer style="background-color: var(--border-color); padding: <?php echo $show_full_footer ? '4rem 2rem 2rem 2rem' : '1.5rem 2rem'; ?>; margin-top: 4rem; width: 100%;">
        <?php if ($show_full_footer): ?>
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(3, 1fr); gap: 4rem; text-align: left; margin-bottom: 3rem;">
            <div style="border-right: 1px solid rgba(0,0,0,0.1); padding-right: 2rem;">
                <h4 style="color: var(--text-main); font-size: 1.25rem; margin-bottom: 1rem;">Sup Tulang ZZ</h4>
                <p style="color: var(--text-main); font-size: 0.95rem; line-height: 1.6;">
                    Established in 1990, we serve authentic Johor-style mutton bone marrow soup and rich noodle dishes. Our legendary recipes have been passed down for generations, bringing you the true taste of tradition.
                </p>
            </div>
            <div style="border-right: 1px solid rgba(0,0,0,0.1); padding-right: 2rem;">
                <h4 style="color: var(--text-main); font-size: 1.25rem; margin-bottom: 1rem;">Services</h4>
                <p style="color: var(--text-main); font-size: 0.95rem; line-height: 1.6;">
                    Whether you're dining in, picking up your meal, or opting for delivery, our streamlined digital ordering system ensures a seamless experience. We cater to all your cravings, fresh and fast.
                </p>
            </div>
            <div>
                <h4 style="color: var(--text-main); font-size: 1.25rem; margin-bottom: 1rem;">Contact</h4>
                <p style="color: var(--text-main); font-size: 0.95rem; line-height: 1.6;">
                    <strong>Location:</strong> 123 Food Street, Culinary District, Johor Bahru, 80000<br>
                    <strong>Phone:</strong> +60 12-345 6789<br>
                    <strong>Email:</strong> hello@suptulangzz.com<br>
                    <strong>Hours:</strong> Open Daily 10 AM - 10 PM
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="max-width: 1200px; margin: 0 auto; <?php echo $show_full_footer ? 'padding-top: 2rem; border-top: 1px solid rgba(0,0,0,0.1);' : ''; ?> text-align: center;">
            <p style="color: var(--text-muted); font-size: 0.85rem;">
                &copy; <?php echo date('Y'); ?> <strong>Sup Tulang ZZ</strong>. All rights reserved. Authentic Mutton Bone Soup since 1990.
            </p>
        </div>
    </footer>
    
    <script src="js/app.js"></script>
</body>
</html>
