<?php
require_once 'process/db.php';

// Fetch categories
$categories_res = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories = [];
while ($row = $categories_res->fetch_assoc()) {
    $categories[] = $row;
}

// Category filter from URL
$active_category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build product query
$sql = "SELECT * FROM products";
if (!empty($active_category)) {
    $sql .= " WHERE category_slug = '" . $conn->real_escape_string($active_category) . "'";
}
$sql .= " ORDER BY id DESC";

$products_res = $conn->query($sql);
$products = [];
if ($products_res) {
    while ($row = $products_res->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .filter-sidebar {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 100px;
        }

        .filter-section {
            margin-bottom: 2rem;
        }

        .filter-section h6 {
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.8rem;
            margin-bottom: 1.2rem;
            color: var(--primary-color);
        }

        .color-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            cursor: pointer;
            border: 1px solid #ddd;
        }

        .search-bar-inline {
            background: #fff;
            padding: 1rem 2rem;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            margin-bottom: 3rem;
        }

        .search-bar-inline input {
            border: none;
            width: 100%;
            margin-left: 1rem;
            font-size: 1.1rem;
        }

        .search-bar-inline input:focus {
            outline: none;
        }
    </style>
</head>

<body>

    <?php 
    $active_page = 'products';
    require_once 'includes/navbar.php'; 
    ?>

    <!-- Page Header -->
    <section class="py-5 mt-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-3 fw-bold mb-0">Our Collection</h1>
                    <p class="lead text-muted mt-3">Explore our curated selection of premium pieces.</p>
                </div>
                <div class="col-md-6 mt-4 mt-md-0">
                    <div class="search-bar-inline">
                        <i class="bi bi-search text-muted h4 mb-0"></i>
                        <input type="text" id="inlineSearchInput" placeholder="Advanced search for products...">
                        <button class="btn btn-premium btn-sm rounded-pill px-3" id="inlineSearchBtn">Search</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop Content -->
    <section class="pb-5">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="filter-sidebar">
                        <div class="filter-section">
                            <h6>Categories</h6>
                            <?php foreach ($categories as $index => $cat): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input category-filter-checkbox" type="checkbox" 
                                        id="cat-<?= $cat['id']; ?>" 
                                        value="<?= htmlspecialchars($cat['slug']); ?>"
                                        <?= ($active_category === $cat['slug']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cat-<?= $cat['id']; ?>">
                                        <?= htmlspecialchars($cat['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="filter-section">
                            <h6>Price Range</h6>
                            <input type="range" class="form-range" min="0" max="1000" id="priceRange">
                            <div class="d-flex justify-content-between">
                                <span class="small text-muted">$0</span>
                                <span class="small text-muted">$1000</span>
                            </div>
                        </div>

                        <div class="filter-section">
                            <h6>Colors</h6>
                            <div class="d-flex flex-wrap">
                                <span class="color-dot" style="background: #000;" title="Black"></span>
                                <span class="color-dot" style="background: #fff;" title="White"></span>
                                <span class="color-dot" style="background: #c5a059;" title="Gold"></span>
                                <span class="color-dot" style="background: #555;" title="Grey"></span>
                                <span class="color-dot" style="background: #1a1a1a;" title="Dark Charcoal"></span>
                            </div>
                        </div>

                        <div class="filter-section">
                            <h6>Sizes</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-dark btn-sm rounded-1">XS</button>
                                <button class="btn btn-outline-dark btn-sm rounded-1">S</button>
                                <button class="btn btn-outline-dark btn-sm rounded-1">M</button>
                                <button class="btn btn-outline-dark btn-sm rounded-1">L</button>
                                <button class="btn btn-outline-dark btn-sm rounded-1">XL</button>
                            </div>
                        </div>

                        <button class="btn btn-dark w-100 rounded-pill py-2 mt-3" onclick="window.location.href='products.php'">Reset Filters</button>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <p class="mb-0 text-muted">Showing <strong><?= count($products); ?></strong> premium pieces</p>
                        <select class="form-select w-auto border-0 bg-light rounded-pill px-4 py-2" id="sortSelect">
                            <option value="newest">Newest First</option>
                            <option value="low-high">Price: Low to High</option>
                            <option value="high-low">Price: High to Low</option>
                        </select>
                    </div>

                    <div class="row g-4" id="productsGridContainer">
                        <?php if (empty($products)): ?>
                            <div class="col-12 text-center py-5">
                                <h3 class="text-muted">No products found matching your selection.</h3>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $prod): ?>
                                <div class="col-md-4 col-6 product-item-col" data-category="<?= htmlspecialchars($prod['category_slug']); ?>">
                                    <div class="product-card">
                                        <div class="product-img-wrapper" style="height: 350px;">
                                            <img src="<?= htmlspecialchars($prod['image_url']); ?>" alt="<?= htmlspecialchars($prod['name']); ?>">
                                            <div class="product-actions">
                                                <button class="action-btn fav-btn" title="Add to Wishlist"><i
                                                        class="bi bi-heart"></i></button>
                                                <button class="action-btn cart-btn" title="Add to Cart"><i
                                                        class="bi bi-bag-plus"></i></button>
                                            </div>
                                        </div>
                                        <div class="product-info">
                                            <h5 class="mb-1"><?= htmlspecialchars($prod['name']); ?></h5>
                                            <p class="mb-2 text-capitalize"><?= htmlspecialchars($prod['category_slug']); ?> • <?= htmlspecialchars($prod['color'] ?: 'Natural'); ?></p>
                                            <h6 class="fw-bold">$<?= number_format($prod['price'], 2); ?></h6>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled"><a class="page-link rounded-circle mx-1" href="#"><i
                                        class="bi bi-chevron-left"></i></a></li>
                            <li class="page-item active"><a class="page-link rounded-circle mx-1" href="#">1</a></li>
                            <li class="page-item"><a class="page-link rounded-circle mx-1" href="#">2</a></li>
                            <li class="page-item"><a class="page-link rounded-circle mx-1" href="#">3</a></li>
                            <li class="page-item"><a class="page-link rounded-circle mx-1" href="#"><i
                                        class="bi bi-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>

    <script>
        // Bind sidebar checkbox redirection
        document.querySelectorAll('.category-filter-checkbox').forEach(box => {
            box.addEventListener('change', function() {
                if (this.checked) {
                    window.location.href = 'products.php?category=' + encodeURIComponent(this.value);
                } else {
                    window.location.href = 'products.php';
                }
            });
        });

        // Simple client side catalog search
        const runSearch = () => {
            const query = document.getElementById('inlineSearchInput').value.trim().toLowerCase();
            const cards = document.querySelectorAll('#productsGridContainer .product-item-col');
            cards.forEach(card => {
                const name = card.querySelector('h5').textContent.toLowerCase();
                const category = card.getAttribute('data-category').toLowerCase();
                if (name.includes(query) || category.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        };

        document.getElementById('inlineSearchBtn')?.addEventListener('click', runSearch);
        document.getElementById('inlineSearchInput')?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') runSearch();
        });
    </script>
</body>

</html>
