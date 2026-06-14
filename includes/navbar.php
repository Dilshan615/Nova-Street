<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure database connection is active
if (!isset($conn)) {
    require_once 'process/db.php';
}
?>
<script>
    const IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<?php

// Fetch categories for the search overlay if not already defined
if (!isset($categories) || empty($categories)) {
    $categories_res = $conn->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = [];
    if ($categories_res) {
        while ($row = $categories_res->fetch_assoc()) {
            $categories[] = $row;
        }
    }
}

// Active page helper fallback
if (!isset($active_page)) {
    $active_page = 'home';
}
?>

<!-- Advanced Search Overlay -->
<div class="search-overlay" id="searchOverlay">
    <div class="search-close" id="searchClose"><i class="bi bi-x-lg"></i></div>
    <div class="search-container">
        <div class="search-input-wrapper">
            <input type="text" id="overlaySearchInput" placeholder="Search Nova Street Collections...">
        </div>
        <div class="search-filters">
            <div class="filter-group">
                <label for="searchCategory">Category</label>
                <select id="searchCategory">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['slug']); ?>"><?= htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="searchPrice">Price Range</label>
                <select id="searchPrice">
                    <option>All Prices</option>
                    <option>$0 - $50</option>
                    <option>$50 - $150</option>
                    <option>$150 - $500</option>
                    <option>$500+</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="searchSort">Sort By</label>
                <select id="searchSort">
                    <option>Newest First</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Most Popular</option>
                </select>
            </div>
        </div>
        <div class="search-results-preview">
            <h6>Quick Suggestions</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="#" class="btn btn-outline-light btn-sm rounded-pill px-3">Summer Dresses</a>
                <a href="#" class="btn btn-outline-light btn-sm rounded-pill px-3">Mens Suits</a>
                <a href="#" class="btn btn-outline-light btn-sm rounded-pill px-3">Kids Jackets</a>
                <a href="#" class="btn btn-outline-light btn-sm rounded-pill px-3">Luxury Watches</a>
            </div>
        </div>
    </div>
</div>

<!-- Cart Drawer -->
<div class="cart-drawer" id="cartDrawer">
    <div class="drawer-header">
        <h5 class="mb-0">Shopping Bag</h5>
        <button class="btn p-0" id="cartClose" aria-label="Close cart"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="drawer-items-container cart-items-container">
        <!-- Items injected via JS -->
    </div>
    <div class="drawer-footer">
        <div class="cart-total">
            <span>Total:</span>
            <span id="cartTotalAmount">$0.00</span>
        </div>
        <div class="d-flex gap-2">
            <a href="cart.php" class="btn btn-premium-outline w-50 py-3 rounded-pill text-decoration-none text-center">View Bag</a>
            <a href="cart.php" class="btn btn-premium w-50 py-3 rounded-pill text-decoration-none text-center">Checkout</a>
        </div>
    </div>
</div>

<!-- Wishlist Drawer -->
<div class="wishlist-drawer" id="wishlistDrawer">
    <div class="drawer-header">
        <h5 class="mb-0">My Wishlist</h5>
        <button class="btn p-0" id="wishlistClose" aria-label="Close wishlist"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="drawer-items-container wishlist-items-container">
        <!-- Wishlist items injected via JS -->
    </div>
    <div class="drawer-footer">
        <div class="d-flex gap-2">
            <a href="wishlist.php" class="btn btn-premium-outline w-50 py-3 rounded-pill text-decoration-none text-center">View List</a>
            <button class="btn btn-premium w-50 py-3 rounded-pill">Add All to Bag</button>
        </div>
    </div>
</div>
<div class="drawer-overlay" id="drawerOverlay"></div>

<!-- Header / Navbar -->
<nav class="navbar navbar-expand-lg fixed-top <?= ($active_page !== 'home') ? 'scrolled' : ''; ?>" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="index.php">Nova Street.</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link <?= ($active_page === 'home') ? 'active' : ''; ?>" href="index.php" id="nav-home">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= ($active_page === 'categories') ? 'active' : ''; ?>" href="categories.php" id="nav-categories">Categories</a></li>
                <li class="nav-item"><a class="nav-link <?= ($active_page === 'products') ? 'active' : ''; ?>" href="products.php" id="nav-products">All Products</a></li>
                <li class="nav-item"><a class="nav-link <?= ($active_page === 'about') ? 'active' : ''; ?>" href="about.php" id="nav-about">About Us</a></li>
                <li class="nav-item"><a class="nav-link <?= ($active_page === 'contact') ? 'active' : ''; ?>" href="index.php#contact" id="nav-contact">Contact</a></li>
            </ul>
        </div>
        <div class="nav-icons d-flex align-items-center">
            <button class="btn position-relative" id="navSearchBtn" title="Search">
                <i class="bi bi-search"></i>
            </button>
            <button class="btn position-relative" id="navWishlistBtn" title="Wishlist">
                <i class="bi bi-heart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark"
                    style="font-size: 0.6rem;">0</span>
            </button>
            <button class="btn position-relative" id="navCartBtn" title="Shopping Bag">
                <i class="bi bi-bag"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size: 0.6rem;">0</span>
            </button>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown d-inline-block">
                    <button class="btn p-0 dropdown-toggle no-caret" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Account" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-circle fs-4"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg text-white" aria-labelledby="userDropdown" style="background: rgba(30, 30, 30, 0.95); backdrop-filter: blur(10px); border-radius: 15px; padding: 10px;">
                        <li><span class="dropdown-item-text text-white-50 small" style="pointer-events: none;">Hello, <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?></span></li>
                        <li><hr class="dropdown-divider bg-secondary opacity-25"></li>
                        <li><a class="dropdown-item text-white hover-accent" href="profile.php" style="border-radius: 8px; font-size: 0.9rem; padding: 6px 12px;"><i class="bi bi-person me-2" style="color: var(--accent-color);"></i>My Profile</a></li>
                        <li><a class="dropdown-item text-white hover-accent" href="delivery_tracking.php" style="border-radius: 8px; font-size: 0.9rem; padding: 6px 12px;"><i class="bi bi-truck me-2" style="color: var(--accent-color);"></i>Track Orders</a></li>
                        <li><hr class="dropdown-divider bg-secondary opacity-25"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php" style="border-radius: 8px; font-size: 0.9rem; padding: 6px 12px;"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn p-0" title="Account" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-person fs-4"></i></a>
            <?php endif; ?>
        </div>
    </div>
</nav>
