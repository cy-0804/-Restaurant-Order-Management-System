# ZZ Sup Tulang - Restaurant Order Management System
### Developer & Team Member Guide

Welcome to the **ZZ Sup Tulang Restaurant Order Management System** skeleton! This project implements the core HTML routing, security gates, payment gateways, and real-time order trackers. It is designed to work in two modes out-of-the-box:

1.  **Database Mode (Production/Dev)**: Processes SQL transactions using MySQL, stores orders/items, and handles uploads of bank transfer receipts.
2.  **Client-Side Mock Mode (Instant Test)**: If MySQL is offline, the system automatically falls back to `localStorage` to emulate database actions. Your team can test customer checkouts, floor maps, and kitchen state updates inside the browser.

---

## 📂 Project Architecture Map

Here is a quick overview of the codebase file structure:

*   **Database & Config**
    *   [`database/schema.sql`](database/schema.sql): Defines the MySQL schemas (`categories`, `menu_items`, `orders`, `order_items`, `users`) and seeds defaults.
    *   [`config/db.php`](config/db.php): Establishes the PDO connection. Auto-creates the database and imports the schema on the first page load.
*   **Routing & Authorization Security**
    *   [`includes/auth.php`](includes/auth.php): Encapsulates session controls and employee checks (`check_role()`).
    *   [`login.php`](login.php): Secure sign-in form with error validations, mock auth fallbacks, and auto-routing.
*   **Shared Templates**
    *   [`includes/header.php`](includes/header.php): Starts session, parses QR table parameters, and matches active tabs to logged-in sessions.
    *   [`includes/footer.php`](includes/footer.php): Closes layouts and loads scripts.
*   **Customer Frontend**
    *   [`index.php`](index.php): Core homepage portal. Auto-routes employees to `staff.php`, table-scans to `menu.php?table=X`, and guests to the online delivery welcome page.
    *   [`menu.php`](menu.php): Category filters, search input bar, menu card grid, and cart additions.
    *   [`cart.php`](cart.php): Item list displaying quantities, controls, and tax sums.
    *   [`checkout.php`](checkout.php): Customer form. Integrates a **Mock DuitNow QR Code** and a **Mock Payment Gateway FPX Pop-Up** that automates verification.
    *   [`order_confirmation.php`](order_confirmation.php): Success details showing a simulated email notification sent to the client.
    *   [`track.php`](track.php): Customer order tracker with a progress stepper polling status changes every 5 seconds.
*   **Employee Backend**
    *   [`staff.php`](staff.php): Consolidated employee dashboard containing:
        *   **Orders Queue View**: Lists incoming orders, verifies bank receipts, and prints formatted kitchen tickets.
        *   **Table Floor Map View**: Displays table occupancy status, table detail sidebars, and launches a **Mobile Camera QR Scanner**.
    *   [`qr_generator.php`](qr_generator.php): Secure utility that generates printable table QR code cards.
    *   [`api.php`](api.php): Secures REST actions (`get_all_orders`, `update_status`, `verify_payment`) and handles transactional submissions.
*   **Core Styling & Client Logic**
    *   [`css/style.css`](css/style.css): Dark-mode glassmorphic design system with gold highlights and printing overrides.
    *   [`js/app.js`](js/app.js): Cart state manager, receipt preview helpers, and mock database controllers.

---

## 🔑 Seeding Credentials

Use these seeded employee accounts to access the staff portals:

| Username | Password | User Role | Access Privileges |
| :--- | :--- | :--- | :--- |
| **`staff1`** | `password123` | `staff` | Orders Queue, Floor Map, QR Code Generator |
| **`staff2`** | `password123` | `staff` | Orders Queue, Floor Map, QR Code Generator |
| **`admin1`** | `password123` | `admin` | Full administrator privileges |

---

## 🚀 How to Set Up and Run

### Option A: Hosting with XAMPP (Full Database Mode)
1.  Copy this project folder into your server root directory:
    *   XAMPP: `C:\xampp\htdocs\-Restaurant-Order-Management-System\`
2.  Open your **XAMPP Control Panel** and start **Apache** and **MySQL**.
3.  Open your browser and navigate to:
    `http://localhost/-Restaurant-Order-Management-System/index.php`
4.  *Note: You do not need to import SQL manually.* The config connection script (`config/db.php`) will auto-detect the lack of tables, create `restaurant_db`, run `schema.sql`, and seed the users and menu items.

### Option B: Built-in PHP Server (Testing)
1.  Open your terminal inside the project root directory.
2.  Start the built-in PHP server:
    ```bash
    php -S localhost:8000
    ```
3.  Navigate to `http://localhost:8000` in your web browser.

---

## 🧪 Step-by-Step Verification Guide

To verify the skeleton operations, follow these four flows:

### Flow 1: Customer Online Delivery (Mock Payment Gateway)
1.  Access `index.php` (normal URL, no parameters). You will land on the customer portal. Click **Start Online Order**.
2.  In the menu, search for "Tulang" and click **Add** on *Sup Tulang* and *Mee Rebus Tulang*.
3.  Go to the **Cart** and click **Proceed to Checkout**.
4.  Fill in your Name, Email, and Delivery Address. Select **Online Banking**.
5.  Click **Pay via Payment Gateway (Mock)**. A popup will load. Select a bank (e.g. Maybank2u) and click **Simulate Payment**.
6.  The popup will close. Observe that the receipt upload container has disappeared, and the submit button displays a green check showing **"Place Order (Paid via Gateway)"**.
7.  Click **Place Order**, verify the receipt summary, and click **Track Your Order** to view the live stepper.

### Flow 2: Customer QR Scan (Dine-In Mode)
1.  Type the scanned table link directly: `http://localhost/-Restaurant-Order-Management-System/index.php?table=4`
2.  The routing engine will redirect you **directly** to `menu.php`. Notice the amber banner at the top: **"🛎️ Walk-In Mode: You are ordering for Table 4."**
3.  Add items to the cart and click checkout.
4.  Notice that the table number is locked to **Table 4**, delivery address fields are removed, and payment is set to **Pay Cash at Counter**. Place the order.

### Flow 3: Kitchen & Table Operations (Staff View)
1.  Navigate to `login.php` and log in with username **`staff1`** and password **`password123`**.
2.  You will land on the **Orders Queue** dashboard. You will see the orders placed in Flow 1 & Flow 2.
    *   For the online order, notice the payment is marked **Verified** (due to the simulated payment gateway).
    *   Click the **Print Kitchen Slip** (printer icon) next to an order. A print window will open containing a kitchen docket.
    *   Click **Accept Order** (moves status to *Preparing*) and then click **Mark Ready**.
3.  Open the customer tracking page from Flow 1—you will see the progress bar update in real-time.
4.  Now, click the **Floor Map & Ordering** tab in the top-right toolbar.
    *   You will see the restaurant floor plan. **Table 4** will be highlighted in **Blue (Occupied)** with its current cooking state.
    *   Click **Table 4**. The sidebar will load its active order details.

### Flow 4: Waiter Camera QR Scan Simulation
1.  While logged in as `staff1`, go to the **Floor Map & Ordering** tab.
2.  Click **Scan Table QR** in the floor map header.
3.  Grant camera permissions. Hold your phone camera up to scan one of the table QR codes generated on your screen (generated in the Staff Sidebar under **Print Table QRs**).
4.  Once scanned, the camera stops, and you are automatically redirected to `menu.php?table=X` to place a waiter-assisted order for that table.

### Flow 5: Manual Banking, Re-Uploads & Rejections
1.  During checkout, select **Online Banking** but upload an image/PDF as a manual bank receipt instead of using the payment gateway. Place the order.
2.  Log in as **`staff1`**. Under the Orders Queue, notice the order has a **Verify Receipt** button. Click it to preview the receipt.
3.  Click **Reject Payment**. Observe that the order now displays an **Unverified** badge, and the customer automatically receives an email notification containing a tracking link.
4.  Open the tracker link from the customer's perspective. Notice the "Payment Failed / Rejected" message and a form to **Re-Upload Receipt**.
5.  Re-upload a new file. The Staff Dashboard automatically updates the order back to a pending state with the **Verify Receipt** button.
6.  Alternatively, if the staff clicks the red **Reject** button on a pending order directly, the order status moves to Cancelled, and the customer receives an "Order Cancelled" email outlining the refund policy.
