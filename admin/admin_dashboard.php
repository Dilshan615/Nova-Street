<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Nova Street</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        :root {
            --bg-color: #f7f7f8;
            --panel-color: #ffffff;
            --accent-color: #c5a059;
            --accent-hover: #b38e47;
            --text-color: #0f0f11;
            --text-muted: #616166;
            --border-color: #e5e5ea;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Outfit', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* Layout Structure */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        /* Sidebar Styling */
        #sidebar {
            min-width: 280px;
            max-width: 280px;
            background: var(--panel-color);
            border-right: 1px solid var(--border-color);
            min-height: 100vh;
            transition: var(--transition);
            z-index: 1000;
        }

        #sidebar.active {
            margin-left: -280px;
        }

        .sidebar-header {
            padding: 2.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-header h3 {
            font-size: 1.8rem;
            font-weight: 300;
            letter-spacing: 2px;
            margin: 0;
        }

        .sidebar-header h3 span {
            color: var(--accent-color);
            font-weight: 600;
        }

        .sidebar-menu {
            padding: 2rem 0;
            list-style: none;
            margin: 0;
        }

        .sidebar-menu li a {
            padding: 1rem 2rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li.active a {
            color: var(--text-color);
            background: rgba(0, 0, 0, 0.03);
            border-left-color: var(--accent-color);
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            font-size: 1.2rem;
        }

        /* Content Page Area */
        #content {
            width: 100%;
            padding: 2.5rem 3.5rem;
            min-height: 100vh;
            transition: var(--transition);
        }

        /* Navbar top toggle button */
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .sidebar-toggle-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 0.6rem 1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
        }

        .sidebar-toggle-btn:hover {
            border-color: var(--accent-color);
            color: var(--accent-color);
        }

        /* Dashboard Overview Cards */
        .stat-card {
            background: var(--panel-color);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(197, 160, 89, 0.2);
        }

        .stat-card-icon {
            position: absolute;
            right: 20px;
            bottom: 15px;
            font-size: 3.5rem;
            color: rgba(197, 160, 89, 0.12);
        }

        .stat-card-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 0.8rem;
        }

        .stat-card-value {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        /* Tables & Lists */
        .panel-box {
            background: var(--panel-color);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
        }

        .panel-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table {
            color: var(--text-color);
            vertical-align: middle;
        }

        .table th {
            color: var(--text-muted);
            font-weight: 500;
            border-bottom-color: var(--border-color);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 1.2rem 1rem;
        }

        .table td {
            border-bottom-color: var(--border-color);
            padding: 1.2rem 1rem;
            font-weight: 300;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Buttons & Actions */
        .btn-premium {
            background-color: var(--accent-color);
            color: #000;
            font-weight: 500;
            border-radius: 50px;
            border: none;
            padding: 0.6rem 1.8rem;
            transition: var(--transition);
        }

        .btn-premium:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
        }

        .btn-premium-outline {
            background-color: transparent;
            color: var(--text-color);
            border: 1px solid var(--border-color);
            font-weight: 500;
            border-radius: 50px;
            padding: 0.6rem 1.8rem;
            transition: var(--transition);
        }

        .btn-premium-outline:hover {
            border-color: var(--accent-color);
            color: var(--accent-color);
        }

        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            margin-right: 5px;
        }

        .btn-action-edit {
            background: rgba(197, 160, 89, 0.1);
            color: var(--accent-color);
        }

        .btn-action-edit:hover {
            background: var(--accent-color);
            color: #000;
        }

        .btn-action-delete {
            background: rgba(235, 77, 75, 0.1);
            color: #eb4d4b;
        }

        .btn-action-delete:hover {
            background: #eb4d4b;
            color: #fff;
        }

        /* Modals glassmorphism style */
        .modal-content {
            background: var(--panel-color);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            color: var(--text-color);
        }

        .modal-header {
            border-bottom-color: var(--border-color);
            padding: 2rem 2rem 1.5rem;
        }

        .modal-footer {
            border-top-color: var(--border-color);
            padding: 1.5rem 2rem 2rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-control,
        .form-select {
            background-color: rgba(0, 0, 0, 0.02);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            border-radius: 12px;
            padding: 0.8rem 1.2rem;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(0, 0, 0, 0.04);
            border-color: var(--accent-color);
            box-shadow: none;
            color: var(--text-color);
        }

        .form-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* View sections toggling */
        .dashboard-section {
            display: none;
        }

        .dashboard-section.active-section {
            display: block;
        }

        .image-preview {
            max-width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            margin-top: 10px;
            display: none;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <!-- Sidebar Navigation -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Nova<span>Street</span>.</h3>
            </div>
            <ul class="sidebar-menu">
                <li class="active" data-section="overview-section">
                    <a><i class="bi bi-speedometer2"></i>Overview</a>
                </li>
                <li data-section="products-section">
                    <a><i class="bi bi-tag"></i>Products</a>
                </li>
                <li data-section="categories-section">
                    <a><i class="bi bi-grid-3x3-gap"></i>Categories</a>
                </li>
                <li data-section="orders-section">
                    <a><i class="bi bi-receipt"></i>Orders</a>
                </li>
                <li data-section="customers-section">
                    <a><i class="bi bi-people"></i>Customers</a>
                </li>
                <li data-section="inquiries-section">
                    <a><i class="bi bi-chat-left-text"></i>Inquiries</a>
                </li>
                <li data-section="newsletters-section">
                    <a><i class="bi bi-envelope-open"></i>Newsletters</a>
                </li>
                <li class="mt-5">
                    <a href="?action=logout" class="text-danger"><i class="bi bi-box-arrow-right"></i>Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Main Page Content -->
        <div id="content">
            <div class="top-navbar">
                <button type="button" id="sidebarCollapse" class="sidebar-toggle-btn">
                    <i class="bi bi-list"></i> Menu
                </button>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted">Admin User:</span>
                    <span class="fw-bold text-accent-color"><?= htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
            </div>

            <!-- VIEW 1: OVERVIEW / STATS -->
            <div id="overview-section" class="dashboard-section active-section">
                <h2 class="mb-4 fw-bold">Overview</h2>
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-card-title">Total Sales</div>
                            <div class="stat-card-value text-accent-color" id="stat-sales">$0.00</div>
                            <i class="bi bi-currency-dollar stat-card-icon"></i>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="stat-card">
                            <div class="stat-card-title">Orders</div>
                            <div class="stat-card-value" id="stat-orders">0</div>
                            <i class="bi bi-receipt stat-card-icon"></i>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="stat-card">
                            <div class="stat-card-title">Customers</div>
                            <div class="stat-card-value" id="stat-users">0</div>
                            <i class="bi bi-people stat-card-icon"></i>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="stat-card">
                            <div class="stat-card-title">Inquiries</div>
                            <div class="stat-card-value" id="stat-inquiries">0</div>
                            <i class="bi bi-chat-left-text stat-card-icon"></i>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="stat-card">
                            <div class="stat-card-title">Newsletters</div>
                            <div class="stat-card-value" id="stat-newsletters">0</div>
                            <i class="bi bi-envelope-open stat-card-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="panel-box">
                    <div class="panel-title">System Status</div>
                    <p class="text-muted">The system connection to the database `nova_street` is running normally. All updates made here are visible in real-time on client storefront pages.</p>
                </div>
            </div>

            <!-- VIEW 2: PRODUCTS -->
            <div id="products-section" class="dashboard-section">
                <div class="panel-box">
                    <div class="panel-title">
                        <span>Products Management</span>
                        <button class="btn btn-premium btn-sm" onclick="showAddProductModal()">Add Product</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Color / Swatch</th>
                                    <th>Sizes Available</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VIEW 3: CATEGORIES -->
            <div id="categories-section" class="dashboard-section">
                <div class="panel-box">
                    <div class="panel-title">
                        <span>Categories Management</span>
                        <button class="btn btn-premium btn-sm" onclick="showAddCategoryModal()">Add Category</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Category Name</th>
                                    <th>Slug / Identifier</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VIEW 4: ORDERS -->
            <div id="orders-section" class="dashboard-section">
                <div class="panel-box">
                    <div class="panel-title">Orders History</div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <th>Date Placed</th>
                                    <th>Items Summary</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VIEW 5: CUSTOMERS -->
            <div id="customers-section" class="dashboard-section">
                <div class="panel-box">
                    <div class="panel-title">Registered Customer Directory</div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email Address</th>
                                    <th>Contact Number</th>
                                    <th>Gender</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody id="customersTableBody">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VIEW 6: INQUIRIES -->
            <div id="inquiries-section" class="dashboard-section">
                <div class="panel-box">
                    <div class="panel-title">Customer Inquiries / Contacts</div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email Address</th>
                                    <th>Subject</th>
                                    <th>Message Context</th>
                                    <th>Received Date</th>
                                </tr>
                            </thead>
                            <tbody id="inquiriesTableBody">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VIEW 7: NEWSLETTERS -->
            <div id="newsletters-section" class="dashboard-section">
                <div class="panel-box">
                    <div class="panel-title">Newsletter Subscriptions</div>
                    <div class="table-responsive" style="max-width: 600px;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Email Address</th>
                                    <th>Subscribed Date</th>
                                </tr>
                            </thead>
                            <tbody id="newslettersTableBody">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUCT MODAL -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="productForm">
                    <input type="hidden" id="productId" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="productModalLabel">Add New Product</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="productName">Product Name *</label>
                                <input type="text" class="form-control" id="productName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="productCategory">Category *</label>
                                <select class="form-select" id="productCategory" required>
                                    <!-- Populated via JS categories -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="productPrice">Price ($) *</label>
                                <input type="number" step="0.01" class="form-control" id="productPrice" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="productImageUrl">Product Image *</label>
                                <select class="form-select" id="productImageUrl" onchange="previewImage(this.value, 'productImgPreview')" required>
                                    <option value="assets/img/summer.png">Summer Dress/Relaxed Pants (assets/img/summer.png)</option>
                                    <option value="assets/img/men.png">Men's Suits (assets/img/men.png)</option>
                                    <option value="assets/img/hero.png">Women's Top/Creative (assets/img/hero.png)</option>
                                    <option value="assets/img/winter.png">Winter Cashmere (assets/img/winter.png)</option>
                                    <option value="assets/img/accessories.png">Accessories (assets/img/accessories.png)</option>
                                </select>
                                <img id="productImgPreview" class="image-preview" src="" alt="preview">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="productColor">Color / Swatch Name</label>
                                <input type="text" class="form-control" id="productColor" placeholder="e.g. Light White, Natural">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="productSize">Sizes (comma-separated)</label>
                                <input type="text" class="form-control" id="productSize" placeholder="e.g. S, M, L">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="productDescription">Description</label>
                                <textarea class="form-control" id="productDescription" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-premium">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- CATEGORY MODAL -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="categoryForm">
                    <input type="hidden" id="categoryId" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="categoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="categoryName">Category Name *</label>
                                <input type="text" class="form-control" id="categoryName" oninput="generateSlug(this.value)" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="categorySlug">Slug / Identifier (Unique) *</label>
                                <input type="text" class="form-control" id="categorySlug" placeholder="e.g. women, kids" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="categoryImageUrl">Category Image *</label>
                                <select class="form-select" id="categoryImageUrl" onchange="previewImage(this.value, 'categoryImgPreview')" required>
                                    <option value="assets/img/hero.png">Women's Top/Creative (assets/img/hero.png)</option>
                                    <option value="assets/img/men.png">Men's Suits (assets/img/men.png)</option>
                                    <option value="assets/img/summer.png">Summer/Kids (assets/img/summer.png)</option>
                                    <option value="assets/img/winter.png">Winter Cashmere (assets/img/winter.png)</option>
                                    <option value="assets/img/accessories.png">Accessories (assets/img/accessories.png)</option>
                                </select>
                                <img id="categoryImgPreview" class="image-preview" src="" alt="preview">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="categoryDescription">Short Description</label>
                                <input type="text" class="form-control" id="categoryDescription">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-premium">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Collapsible sidebar
        document.getElementById('sidebarCollapse').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Tabs menu toggle action
        const menuItems = document.querySelectorAll('.sidebar-menu li');
        const sections = document.querySelectorAll('.dashboard-section');

        menuItems.forEach(item => {
            item.addEventListener('click', function () {
                if (this.classList.contains('mt-5')) return; // Ignore logout menu
                menuItems.forEach(el => el.classList.remove('active'));
                this.classList.add('active');

                const targetSec = this.getAttribute('data-section');
                sections.forEach(sec => {
                    if (sec.id === targetSec) {
                        sec.classList.add('active-section');
                    } else {
                        sec.classList.remove('active-section');
                    }
                });

                // Run corresponding load scripts on click
                if (targetSec === 'overview-section') loadStats();
                if (targetSec === 'products-section') loadProducts();
                if (targetSec === 'categories-section') loadCategories();
                if (targetSec === 'orders-section') loadOrders();
                if (targetSec === 'customers-section') loadCustomers();
                if (targetSec === 'inquiries-section') loadInquiries();
                if (targetSec === 'newsletters-section') loadNewsletters();
            });
        });

        // Dynamic API Load Scripts
        const loadStats = () => {
            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_admin_stats' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    document.getElementById('stat-sales').textContent = `$${stats.total_sales.toFixed(2)}`;
                    document.getElementById('stat-orders').textContent = stats.total_orders;
                    document.getElementById('stat-users').textContent = stats.total_users;
                    document.getElementById('stat-inquiries').textContent = stats.total_inquiries;
                    document.getElementById('stat-newsletters').textContent = stats.total_newsletters;
                }
            })
            .catch(console.error);
        };

        let cachedCategories = [];

        const loadProducts = () => {
            // First load categories for selector binding
            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_categories' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cachedCategories = data.categories;
                    const select = document.getElementById('productCategory');
                    select.innerHTML = data.categories.map(cat => 
                        `<option value="${cat.slug}">${cat.name}</option>`
                    ).join('');
                }
            })
            .then(() => {
                // Next load products list
                return fetch('../process/process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_products' })
                });
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('productsTableBody');
                    if (data.products.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No products found. Add your first item.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.products.map(prod => {
                        const imgUrl = prod.image_url.startsWith('http') || prod.image_url.startsWith('data:') || prod.image_url.startsWith('../') ? prod.image_url : '../' + prod.image_url;
                        return `
                            <tr>
                                <td><img src="${imgUrl}" style="width: 50px; height: 60px; object-fit: cover; border-radius: 8px;"></td>
                                <td class="fw-bold">${escapeHTML(prod.name)}</td>
                                <td><span class="badge bg-dark px-3 py-2 text-capitalize">${escapeHTML(prod.category_slug)}</span></td>
                                <td class="text-accent-color fw-bold">$${parseFloat(prod.price).toFixed(2)}</td>
                                <td>${escapeHTML(prod.color || '-')}</td>
                                <td>${escapeHTML(prod.size || '-')}</td>
                                <td>
                                    <button class="btn-action btn-action-edit" onclick="showEditProductModal(${JSON.stringify(prod).replace(/"/g, '&quot;')})"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn-action btn-action-delete" onclick="deleteProduct(${prod.id})"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            })
            .catch(console.error);
        };

        const loadCategories = () => {
            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_categories' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('categoriesTableBody');
                    if (data.categories.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No categories found. Add one.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.categories.map(cat => {
                        const imgUrl = cat.image_url.startsWith('http') || cat.image_url.startsWith('data:') || cat.image_url.startsWith('../') ? cat.image_url : '../' + cat.image_url;
                        return `
                            <tr>
                                <td><img src="${imgUrl}" style="width: 60px; height: 40px; object-fit: cover; border-radius: 8px;"></td>
                                <td class="fw-bold">${escapeHTML(cat.name)}</td>
                                <td class="text-accent-color font-monospace">${escapeHTML(cat.slug)}</td>
                                <td>${escapeHTML(cat.description || '-')}</td>
                                <td>
                                    <button class="btn-action btn-action-edit" onclick="showEditCategoryModal(${JSON.stringify(cat).replace(/"/g, '&quot;')})"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn-action btn-action-delete" onclick="deleteCategory(${cat.id})"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            })
            .catch(console.error);
        };

        const loadOrders = () => {
            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_orders' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('ordersTableBody');
                    if (data.orders.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No orders found.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.orders.map(order => {
                        let itemsText = '';
                        try {
                            const items = JSON.parse(order.items_json);
                            // Group duplicate items by name
                            const counts = {};
                            items.forEach(item => counts[item.name] = (counts[item.name] || 0) + 1);
                            itemsText = Object.entries(counts).map(([name, qty]) => `${name} (x${qty})`).join(', ');
                        } catch(e) {
                            itemsText = 'Failed parsing items data';
                        }
                        return `
                            <tr>
                                <td class="fw-bold font-monospace">${order.order_number}</td>
                                <td>${order.created_at}</td>
                                <td class="text-muted small">${escapeHTML(itemsText)}</td>
                                <td class="text-accent-color fw-bold">$${parseFloat(order.total_amount).toFixed(2)}</td>
                            </tr>
                        `;
                    }).join('');
                }
            })
            .catch(console.error);
        };

        const loadCustomers = () => {
            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_users' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('customersTableBody');
                    if (data.users.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No customers registered yet.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.users.map(user => `
                        <tr>
                            <td>${user.id}</td>
                            <td class="fw-bold">${escapeHTML(user.first_name + ' ' + user.last_name)}</td>
                            <td>${escapeHTML(user.email)}</td>
                            <td>${escapeHTML(user.contact)}</td>
                            <td class="text-capitalize">${user.gender}</td>
                            <td>${user.created_at}</td>
                        </tr>
                    `).join('');
                }
            })
            .catch(console.error);
        };

        const loadInquiries = () => {
            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_contacts' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('inquiriesTableBody');
                    if (data.contacts.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No customer inquiries.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.contacts.map(c => `
                        <tr>
                            <td class="fw-bold">${escapeHTML(c.name)}</td>
                            <td><a href="mailto:${c.email}" class="text-decoration-none text-accent-color">${escapeHTML(c.email)}</a></td>
                            <td class="fw-bold">${escapeHTML(c.subject)}</td>
                            <td class="text-muted small" style="max-width: 400px; word-wrap: break-word;">${escapeHTML(c.message)}</td>
                            <td>${c.created_at}</td>
                        </tr>
                    `).join('');
                }
            })
            .catch(console.error);
        };

        const loadNewsletters = () => {
            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_newsletters' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('newslettersTableBody');
                    if (data.newsletters.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No newsletter subscriptions.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.newsletters.map(n => `
                        <tr>
                            <td class="fw-bold font-monospace">${escapeHTML(n.email)}</td>
                            <td>${n.created_at}</td>
                        </tr>
                    `).join('');
                }
            })
            .catch(console.error);
        };

        // --- Image Preview Logic ---
        const resolveImgUrl = (url) => {
            if (!url) return '';
            if (url.startsWith('http') || url.startsWith('data:') || url.startsWith('../')) {
                return url;
            }
            return '../' + url;
        };

        const previewImage = (val, previewId) => {
            const preview = document.getElementById(previewId);
            if (val) {
                preview.src = resolveImgUrl(val);
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        };

        // --- Category Modal Control ---
        const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));

        const showAddCategoryModal = () => {
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categorySlug').value = '';
            document.getElementById('categorySlug').disabled = false;
            document.getElementById('categoryDescription').value = '';
            document.getElementById('categoryImageUrl').value = 'assets/img/hero.png';
            document.getElementById('categoryModalLabel').textContent = 'Add New Category';
            previewImage('assets/img/hero.png', 'categoryImgPreview');
            categoryModal.show();
        };

        const showEditCategoryModal = (cat) => {
            document.getElementById('categoryId').value = cat.id;
            document.getElementById('categoryName').value = cat.name;
            document.getElementById('categorySlug').value = cat.slug;
            document.getElementById('categorySlug').disabled = true; // Slug shouldn't change
            document.getElementById('categoryDescription').value = cat.description || '';
            document.getElementById('categoryImageUrl').value = cat.image_url;
            document.getElementById('categoryModalLabel').textContent = 'Edit Category Details';
            previewImage(cat.image_url, 'categoryImgPreview');
            categoryModal.show();
        };

        const generateSlug = (val) => {
            const id = document.getElementById('categoryId').value;
            if (id === '') { // Auto generate only for new ones
                const slugInput = document.getElementById('categorySlug');
                slugInput.value = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
        };

        document.getElementById('categoryForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('categoryId').value;
            const name = document.getElementById('categoryName').value.trim();
            const slug = document.getElementById('categorySlug').value.trim();
            const description = document.getElementById('categoryDescription').value.trim();
            const imageUrl = document.getElementById('categoryImageUrl').value;

            const action = id ? 'edit_category' : 'add_category';
            const payload = { action, name, slug, description, image_url: imageUrl };
            if (id) payload.id = id;

            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    categoryModal.hide();
                    loadCategories();
                }
            })
            .catch(console.error);
        });

        const deleteCategory = (id) => {
            if (confirm("Are you sure you want to delete this category? Any products in this category might not render correctly.")) {
                fetch('../process/process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_category', id })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) loadCategories();
                })
                .catch(console.error);
            }
        };

        // --- Product Modal Control ---
        const productModal = new bootstrap.Modal(document.getElementById('productModal'));

        const showAddProductModal = () => {
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productImageUrl').value = 'assets/img/summer.png';
            document.getElementById('productColor').value = '';
            document.getElementById('productSize').value = '';
            document.getElementById('productDescription').value = '';
            document.getElementById('productModalLabel').textContent = 'Add New Product';
            previewImage('assets/img/summer.png', 'productImgPreview');
            productModal.show();
        };

        const showEditProductModal = (prod) => {
            document.getElementById('productId').value = prod.id;
            document.getElementById('productName').value = prod.name;
            document.getElementById('productCategory').value = prod.category_slug;
            document.getElementById('productPrice').value = prod.price;
            document.getElementById('productImageUrl').value = prod.image_url;
            document.getElementById('productColor').value = prod.color || '';
            document.getElementById('productSize').value = prod.size || '';
            document.getElementById('productDescription').value = prod.description || '';
            document.getElementById('productModalLabel').textContent = 'Edit Product Details';
            previewImage(prod.image_url, 'productImgPreview');
            productModal.show();
        };

        document.getElementById('productForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('productId').value;
            const name = document.getElementById('productName').value.trim();
            const category_slug = document.getElementById('productCategory').value;
            const price = parseFloat(document.getElementById('productPrice').value);
            const imageUrl = document.getElementById('productImageUrl').value;
            const color = document.getElementById('productColor').value.trim();
            const size = document.getElementById('productSize').value.trim();
            const description = document.getElementById('productDescription').value.trim();

            const action = id ? 'edit_product' : 'add_product';
            const payload = { action, name, category_slug, price, image_url: imageUrl, color, size, description };
            if (id) payload.id = id;

            fetch('../process/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    productModal.hide();
                    loadProducts();
                }
            })
            .catch(console.error);
        });

        const deleteProduct = (id) => {
            if (confirm("Are you sure you want to delete this product?")) {
                fetch('../process/process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_product', id })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) loadProducts();
                })
                .catch(console.error);
            }
        };

        // Safe HTML injection escapes
        const escapeHTML = (str) => {
            if (!str) return '';
            return str.replace(/[&<>'"]/g, 
                tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
            );
        };

        // Load dashboard summary count metrics on load
        loadStats();
    </script>
</body>

</html>
