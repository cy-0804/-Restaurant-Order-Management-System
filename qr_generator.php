<?php
require_once 'includes/auth.php';
check_role('staff');

include 'config/db.php';
include 'includes/header.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$base_url = "$protocol://$host$path";
?>

<style>
    /* Printing Layout variables */
    @media print {
        header, footer, .no-print {
            display: none !important;
        }
        body {
            background: #fff !important;
            color: #000 !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .qr-print-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
        }
        .qr-card {
            background: #fff !important;
            border: 2px solid #000 !important;
            color: #000 !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
        }
        .qr-title {
            color: #000 !important;
        }
        .qr-link-text {
            color: #555 !important;
        }
    }
</style>

<div class="no-print" style="margin-bottom: 2rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>Table QR Code Generator</h2>
        <p style="color:var(--text-muted);">Generate and print QR codes for dine-in customers to place orders directly.</p>
    </div>
    <div style="display:flex; gap:1rem;">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Print Table Cards
        </button>
        <a href="staff.php" class="btn btn-secondary">
            Back to Dashboard
        </a>
    </div>
</div>



<div class="qr-print-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 2rem;">
    <?php for ($table_id = 1; $table_id <= 8; $table_id++): 
        $target_url = "$base_url/index.php?table=$table_id";
        $qr_img_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&color=0-0-0&bgcolor=255-255-255&margin=10&data=" . urlencode($target_url);
    ?>
        <div class="glass-card qr-card" style="text-align: center; padding: 2rem; display: flex; flex-direction: column; align-items: center; justify-content: space-between; border-color: rgba(245, 158, 11, 0.25);">
            <div>
                <div class="logo-icon" style="font-size: 1.5rem; color: var(--primary); margin-bottom: 0.5rem;"><i class="fa-solid fa-bowl-hot"></i></div>
                <h3 class="qr-title" style="margin-bottom: 0.25rem; font-size: 1.25rem;">SUP TULANG ZZ</h3>
                <span class="status-badge preparing" style="font-size: 0.8rem; padding: 0.15rem 0.6rem; font-weight: 700; background: rgba(245, 158, 11, 0.1); border-color: var(--border-color); color: var(--primary);">
                    TABLE <?php echo $table_id; ?>
                </span>
            </div>
            
            <div style="margin: 1.5rem 0; padding: 10px; background: white; border-radius: var(--radius-sm); display: inline-block; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                <img src="<?php echo $qr_img_url; ?>" alt="QR Code Table <?php echo $table_id; ?>" style="width: 180px; height: 180px; display: block;">
            </div>
            
            <div>
                <p style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Scan to Order</p>
                <p class="qr-link-text" style="font-size: 0.75rem; color: var(--text-muted); word-break: break-all; max-width: 220px;">
                    <?php echo htmlspecialchars($target_url); ?>
                </p>
            </div>
        </div>
    <?php endfor; ?>
</div>

<?php
include 'includes/footer.php';
?>
