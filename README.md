# Aura Shop v2.0 - E-Commerce Platform Documentation

A complete, high-performance e-commerce platform custom-tailored for the Algerian market. The project features a customer-facing storefront and an administration dashboard, built using pure PHP, HTML, CSS (Tailwind CDN), vanilla JavaScript, and Supabase (PostgreSQL). It is optimized for serverless deployment on Vercel.

---

## 1. Project Architecture & Directory Structure

```text
aurashopv2.0/
├── api/                  # All backend controllers and templates (Vercel Functions)
│   ├── config.php        # Core system configuration and helpers
│   ├── lang.php          # Multilingual translations (French/English)
│   ├── header.php        # Global navigation header component
│   ├── footer.php        # Global footer & newsletter component
│   ├── set_lang.php      # Language switcher controller
│   ├── create_admin.php  # Administrative bootstrapping utility
│   ├── index.php         # Homepage (Hero collections, featured products)
│   ├── shop.php          # Catalog page with sorting, search, and category filters
│   ├── product.php       # Interactive product page with color variants and swatches
│   ├── cart.php          # Cart overview & promo code management
│   ├── checkout.php      # Order checkout with dynamic delivery fee calculations
│   ├── confirmation.php  # Order completion status screen
│   ├── login.php         # Customer login gateway
│   ├── register.php      # Customer signup controller
│   ├── logout.php        # Session clearance controller
│   ├── settings.php      # Customer portal (Profile, Password change, Order history)
│   ├── admin.php         # KPI Dashboard & overview of recent orders
│   ├── admin_add_product.php    # Form to create new products with variants
│   ├── admin_edit_product.php   # Product update controller
│   ├── admin_orders.php         # Customer orders overview & pagination
│   ├── admin_order_detail.php   # Detailed order view & history logs
│   ├── admin_customers.php      # User management (including Banning utilities)
│   ├── admin_categories.php     # Rayon (category) management utilities
│   ├── admin_promo.php          # Promotional code manager
│   └── admin_settings.php       # Algerian shipping fees administrator (58 Wilayas)
├── assets/               # Public assets (served as static files by Vercel)
│   └── js/
│       ├── cart.js       # Client-side cart cookie manager and totals engine
│       ├── product.js    # Interactive color swatch layout gallery selector
│       ├── checkout.js   # Dynamic delivery zone listener
│       └── admin-product.js # Admin variant builder & media encoder
└── vercel.json           # Vercel serverless configuration and path routing rules
```

---

## 2. Platform Core Architecture & Operations

### 2.1 Serverless Deployment (Vercel Integration)
Since Vercel is a serverless hosting environment without persistent storage or long-running PHP environments, we use the `vercel-php@0.9.0` community builder inside `vercel.json`.
* **Execution**: All `.php` entry points reside in the `api/` folder. Vercel compiles each PHP file in this folder into an independent serverless function.
* **Routing**: The `routes` rules catch requests from the root URL and map them to their corresponding function (e.g. `your-domain.com/shop` rewrites to `/api/shop.php`).
* **Static Assets**: Requests hitting `/assets/...` bypass serverless functions and serve static resources directly from Vercel's global CDN for maximum speed.

### 2.2 Database Layer (Supabase PostgreSQL)
Connecting to database instances securely over the cloud uses a single PostgreSQL Connection URI (`DATABASE_URL`). Connection pools and query execution are managed in `api/config.php` using PHP Data Objects (PDO).

### 2.3 Stateless Session & Authentication Mechanics
Traditional PHP sessions (`$_SESSION`) rely on local file storage, which is incompatible with Vercel's stateless, multi-instance serverless functions.
* **Token-based Cookie Auth**: When a customer logs in, a secure JSON web token or signed browser cookie containing the user ID is created.
* **Security**: Cookies are marked as `HttpOnly` and `Secure` to protect user sessions from cross-site scripting (XSS) attacks.

### 2.4 Cart Logic (Cookie-based Offline Cart)
To optimize performance and minimize database reads, the shopping cart is managed entirely client-side using a browser cookie (`cart`).
* **Format**: The cart stores a JSON array of selected items, quantities, and variants.
* **Consistency**: Managed by `assets/js/cart.js`, updating product amounts immediately in the user's browser, then synced to `api/checkout.php` upon form submission.


---

## 3. Step-by-Step File Explanations

### 3.1 Platform Core Controls

#### `vercel.json`
Specifies how Vercel hosts the application. Tells Vercel to route static file requests (JS/CSS) to the `/assets` folder, compile all `.php` files in `/api` as PHP 8.x functions, and route clean user URLs (like `/shop` or `/cart`) to the serverless backend.

#### `api/config.php`
Initializes database connections and provides helper functions used across all pages:
* **`db_connect()`**: Opens a secure PDO PostgreSQL connection.
* **`format_price($amount)`**: Formats a raw number into Dinar format (e.g. `1 500,00 DA`).
* **Authentication checks**: Contains `requireLogin()` and `requireAdmin()` to restrict page access based on user roles.

#### `api/lang.php`
A structured translation dictionary for French (`fr`) and English (`en`). It exports a global translation array (`$translations`) representing UI elements, form placeholders, checkout steps, and error notifications.

#### `api/set_lang.php`
A backend redirect controller. It receives a query parameter (e.g., `?lang=fr`), updates the user's language preference cookie, and immediately redirects the browser back to the referring page.

#### `api/header.php` & `api/footer.php`
The global template layouts. The header builds the responsive dropdown navbar, category list, search bar, language toggle, and live cart counter. The footer displays newsletter forms, contact specs, and Algerian legal terms.

---

### 3.2 Client-Side Interactive JavaScript

#### `assets/js/cart.js`
* **Local Storage**: Reads, parses, and writes cart JSON objects in browser cookies.
* **UI Controls**: Handles operations to increment/decrement quantities and remove items on the cart page without page reloads.

#### `assets/js/product.js`
* **Color galleries**: Monitors color-swatch selection on product pages. It swaps active visible image galleries depending on the chosen color variant.

#### `assets/js/checkout.js`
* **Delivery Engine**: Listens to changes in the "Wilaya" selection list. It communicates with the backend, retrieves the corresponding home or relay delivery price, and updates the order's grand total in real-time.

#### `assets/js/admin-product.js`
* **Variant Builder**: An admin-side helper that allows administrators to dynamically add new color swatches, select color hex values, and read image uploads as base64 strings so they can be bundled together and saved to the database.

---

### 3.3 Customer Storefront

#### `api/index.php`
The main entrance point. Queries the database to display the latest active product collections, new arrivals, and standard promotions.

#### `api/shop.php`
The shop catalog. Retrieves products from the database using server-side filtering (by category, size range, price limits, and search inputs).

#### `api/product.php`
Displays individual product information. It retrieves variants, size options, and secondary image swatches dynamically, presenting them in a responsive, high-end gallery interface.

#### `api/cart.php`
Renders current items in the user's cart cookie. Checks promo code inputs and cross-references active rules in the database to calculate discounts.

#### `api/checkout.php`
Presents the shipping forms. Autocompletes wilaya fees, records user details, verifies that stock levels are sufficient, inserts order items into the database, decrements inventory levels, and clears the cart cookie on success.

#### `api/confirmation.php`
The success confirmation landing page. Retrieves the order status and invoice summary for the customer who just completed a transaction.

#### `api/login.php` & `api/register.php` & `api/logout.php`
Handles customer identity management. Implements standard credentials validation, hashed password comparison, sign-up controls, and cookie clearance.

#### `api/settings.php`
Contains the customer settings panel. Provides tools to update user profiles, change active passwords, view order history tables, or delete the account permanently.

---

### 3.4 Administration Panel (`api/admin...`)

#### `api/admin.php`
Displays overall e-commerce health metrics: Gross Revenue, total orders processed, database statistics, low-stock warnings, and recent transaction logs.

#### `api/admin_add_product.php` & `api/admin_edit_product.php`
Back-end forms for inventory management. Supports inputting multiple color profiles, setting distinct image galleries for each color variant, and selecting size availability.

#### `api/admin_orders.php` & `api/admin_order_detail.php`
Managers can view all orders, check order items, change order statuses (e.g., Pending -> Shipped -> Completed), and add shipping update comments.

#### `api/admin_customers.php`
Allows administrators to search user records, inspect customer value (total spent/orders placed), and toggle the `is_banned` status of any user.

#### `api/admin_categories.php`
Enables managers to add or remove category sections. Prevents deleting categories that currently contain active products to maintain database integrity.

#### `api/admin_promo.php`
Enables configuration of marketing campaigns by creating, editing, or revoking promo codes (supports expiry dates, maximum usage caps, and minimum order values).

#### `api/admin_settings.php`
Allows administrative adjustments to shipping fees and delivery timetables across the 58 Algerian wilayas.

---

## 4. Key Academic & System Design Highlights

1. **Stateless Operations**: Built to run as serverless microservices on Vercel without relying on local server state or session persistence.
2. **Client-Side Optimization**: Shopping cart mechanics are kept client-side using structured cookies, reducing server overhead.
3. **Database Integrity**: Full relational safety using PostgreSQL constraints, ensuring categories and products are not orphaned and orders map correctly to customers.
4. **Localization**: Fully localized for the Algerian market, including pre-loaded configurations and fees for all 58 wilayas and custom-tailored DA (Dinar) formatting.
