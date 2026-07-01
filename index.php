<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header("Location: staff.php");
    exit;
}

if (isset($_GET['reset_session'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

if (isset($_GET['table'])) {
    $_SESSION['table_number'] = intval($_GET['table']);
    $_SESSION['order_type'] = 'walk-in';
    
    header("Location: menu.php");
    exit;
} else {
    $_SESSION['order_type'] = 'online';
    unset($_SESSION['table_number']);
}

include 'includes/header.php';
?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin-bottom: 4rem; align-items: center;">
        <div style="text-align: left;">
            <h1 style="font-size: 3.5rem; margin-bottom: 1.5rem; letter-spacing: -1px; line-height: 1.2;">
                Welcome to <span style="color: var(--primary);">Sup Tulang ZZ</span>
            </h1>
            <p style="color: var(--text-muted); font-size: 1.15rem; margin-bottom: 2rem; line-height: 1.6;">
                Experience Johor's legendary mutton bone marrow soup and noodle dishes, now with streamlined digital ordering. Browse our menu, add items to your shopping cart, and have your order prepared for delivery or takeaway.
            </p>
        </div>
        <div>
            <img src="Resources/zzsuptulang/menuzz/Slide1.jpeg" alt="Sup Tulang ZZ Menu" style="width: 100%; height: auto; border-radius: var(--radius-lg); box-shadow: 0 10px 30px rgba(0,0,0,0.1); object-fit: contain;">
        </div>
    </div>
    
    <div class="portal-grid" style="margin-bottom: 4rem;">
        <div class="glass-card portal-card">
            <div>
                <div class="portal-icon"><i class="fa-solid fa-globe"></i></div>
                <h3>Order Online</h3>
                <p class="portal-desc">
                    Browse our menu, add items to your cart, place your order, and have your order prepared for delivery or takeaway.
                </p>
            </div>
            <button onclick="document.getElementById('order-modal').style.display='flex'" class="btn btn-primary btn-full">
                Start Online Order <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
        
        <div class="glass-card portal-card">
            <div>
                <div class="portal-icon"><i class="fa-solid fa-qrcode"></i></div>
                <h3>Walk-In QR Scan</h3>
                <p class="portal-desc">
                    Scan the QR code at your table to place orders directly to the kitchen. Waiters are also equipped to assist you.
                </p>
            </div>
            <div style="width: 100%;">
                <button onclick="startScanner()" class="btn btn-primary btn-full" style="margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-camera"></i> Scan Table QR Code
                </button>

            </div>
        </div>
    </div>
</div>

<div id="order-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1002; align-items:center; justify-content:center; padding:2rem;">
    <div class="glass-card" style="max-width:400px; width:100%; position:relative; padding:2.5rem 2rem; text-align:center;">
        <button onclick="document.getElementById('order-modal').style.display='none'" style="position:absolute; top:1rem; right:1rem; background:transparent; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted);">&times;</button>
        
        <h3 style="margin-bottom: 0.5rem; font-size: 1.5rem;">How would you like your order?</h3>
        <p style="color:var(--text-muted); font-size:0.95rem; margin-bottom: 2rem;">Please select delivery or pickup to proceed.</p>
        
        <div style="display:flex; flex-direction:column; gap:1rem;">
            <button onclick="setOrderType('delivery')" class="btn btn-primary btn-full" style="padding:1rem;">
                <i class="fa-solid fa-motorcycle" style="font-size:1.25rem;"></i> Delivery
            </button>
            <button onclick="setOrderType('pickup')" class="btn btn-secondary btn-full" style="padding:1rem;">
                <i class="fa-solid fa-bag-shopping" style="font-size:1.25rem;"></i> Pickup (Takeaway)
            </button>
        </div>
    </div>
</div>

<div id="scanner-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1002; align-items:center; justify-content:center; padding:1rem;">
    <div class="glass-card" style="max-width:500px; width:100%; position:relative; padding:2rem; text-align:center;">
        <button onclick="stopScanner()" style="position:absolute; top:1rem; right:1rem; background:transparent; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted);">&times;</button>
        <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Scan Table QR</h3>
        <div id="reader" style="width: 100%; min-height: 300px; background: #000; border-radius: var(--radius-md); overflow: hidden;"></div>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top: 1rem;">Point your camera at the QR code on your table.</p>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
function setOrderType(type) {
    localStorage.setItem('delivery_method', type);
    window.location.href = 'menu.php';
}

let currentScanner = null;

function startScanner() {
    document.getElementById('scanner-modal').style.display = 'flex';
    
    currentScanner = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    currentScanner.start(
        { facingMode: "environment" }, 
        config, 
        (decodedText, decodedResult) => {
            // success
            stopScanner();
            if(decodedText.includes('table=')) {
                window.location.href = decodedText;
            } else {
                alert("Invalid QR Code: " + decodedText);
            }
        },
        (errorMessage) => {
            // ignore parse errors
        }
    ).catch((err) => {
        console.error("Error starting scanner", err);
        alert("Camera error: Please ensure permissions are granted and you are using HTTPS.");
        document.getElementById('scanner-modal').style.display = 'none';
    });
}

function stopScanner() {
    if (currentScanner) {
        currentScanner.stop().then(() => {
            currentScanner.clear();
            currentScanner = null;
            document.getElementById('scanner-modal').style.display = 'none';
        }).catch(err => {
            console.error("Failed to stop scanner.", err);
            document.getElementById('scanner-modal').style.display = 'none';
        });
    } else {
        document.getElementById('scanner-modal').style.display = 'none';
    }
}
</script>

<?php
include 'includes/footer.php';
?>
