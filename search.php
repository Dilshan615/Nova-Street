<?php
require_once 'process/db.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$color_filter = isset($_GET['color']) ? trim($_GET['color']) : '';
$size_filter = isset($_GET['size']) ? trim($_GET['size']) : '';
$min_price = isset($_GET['min_price']) ? trim($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';

// Build query
$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_slug = c.slug WHERE 1=1";
$params = [];
$types = "";

if ($search_query !== '') {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $q_val = "%$search_query%";
    $params[] = $q_val;
    $params[] = $q_val;
    $types .= "ss";
}

if ($category_filter !== '') {
    $sql .= " AND p.category_slug = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if ($color_filter !== '') {
    $sql .= " AND p.color LIKE ?";
    $c_val = "%$color_filter%";
    $params[] = $c_val;
    $types .= "s";
}

if ($size_filter !== '') {
    $sql .= " AND p.size LIKE ?";
    $s_val = "%$size_filter%";
    $params[] = $s_val;
    $types .= "s";
}

if ($min_price !== '') {
    $sql .= " AND p.price >= ?";
    $params[] = floatval($min_price);
    $types .= "d";
}

if ($max_price !== '') {
    $sql .= " AND p.price <= ?";
    $params[] = floatval($max_price);
    $types .= "d";
}

$sql .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$products = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}
$stmt->close();

// Fetch categories for filter dropdown
$cat_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories_list = [];
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Search | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .search-panel {
            background: #fff;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #e5e5ea;
            margin-bottom: 3rem;
        }

        .filter-header {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>

<body>

    <?php 
    $active_page = 'products';
    require_once 'includes/navbar.php'; 
    ?>

    <!-- Header Section -->
    <section class="py-5 mt-5">
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="display-5 fw-bold mb-1">Global Catalog Search</h1>
                    <p class="lead text-muted">Filter our premium fashion database dynamically.</p>
                </div>
                <div>
                    <button type="button" class="btn btn-premium-outline rounded-pill btn-sm px-4" data-bs-toggle="collapse" data-bs-target="#advFilterCollapse" aria-expanded="false" aria-controls="advFilterCollapse">
                        <i class="bi bi-sliders me-2"></i>Advanced Filters
                    </button>
                </div>
            </div>

            <!-- Search Panel -->
            <div class="search-panel">
                <form action="search.php" method="GET">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="input-group" style="height: 46px;">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill px-3"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" class="form-control bg-light border-start-0 rounded-end-pill px-3" placeholder="Search by name, tags, description...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="category" class="form-select rounded-pill px-4" style="height: 46px;">
                                <option value="">All Collections</option>
                                <?php foreach($categories_list as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $category_filter === $cat['slug'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-premium rounded-pill py-2" style="height: 46px;">Search</button>
                        </div>
                    </div>

                    <!-- Collapsible Filters -->
                    <?php
                    $is_adv_active = ($color_filter !== '' || $size_filter !== '' || $min_price !== '' || $max_price !== '');
                    ?>
                    <div class="collapse <?= $is_adv_active ? 'show' : '' ?> mt-4 pt-4 border-top" id="advFilterCollapse">
                        <h6 class="filter-header"><i class="bi bi-sliders"></i> Parameters</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-muted small">Color Accent</label>
                                <input type="text" name="color" value="<?= htmlspecialchars($color_filter) ?>" class="form-control rounded-pill px-4" placeholder="e.g. Cream, White">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small">Size Profile</label>
                                <select name="size" class="form-select rounded-pill px-4">
                                    <option value="">All Sizes</option>
                                    <option value="XS" <?= $size_filter === 'XS' ? 'selected' : '' ?>>XS</option>
                                    <option value="S" <?= $size_filter === 'S' ? 'selected' : '' ?>>S</option>
                                    <option value="M" <?= $size_filter === 'M' ? 'selected' : '' ?>>M</option>
                                    <option value="L" <?= $size_filter === 'L' ? 'selected' : '' ?>>L</option>
                                    <option value="XL" <?= $size_filter === 'XL' ? 'selected' : '' ?>>XL</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small">Min Price ($)</label>
                                <input type="number" step="0.01" name="min_price" value="<?= htmlspecialchars($min_price) ?>" class="form-control rounded-pill px-4" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small">Max Price ($)</label>
                                <input type="number" step="0.01" name="max_price" value="<?= htmlspecialchars($max_price) ?>" class="form-control rounded-pill px-4" placeholder="500.00">
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <a href="search.php" class="text-muted small text-decoration-none"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filters</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Products Results Grid -->
            <div class="row g-4">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $prod): ?>
                        <div class="col-lg-3 col-md-4 col-6">
                            <div class="product-card">
                                <div class="product-img-wrapper" style="height: 320px;">
                                    <img src="<?= htmlspecialchars(get_image_url($prod['image_url'])) ?>" alt="<?= htmlspecialchars($prod['name']); ?>">
                                    <div class="product-actions">
                                        <button class="action-btn fav-btn" title="Add to Wishlist"><i class="bi bi-heart"></i></button>
                                        <button class="action-btn cart-btn" title="Add to Cart"><i class="bi bi-bag-plus"></i></button>
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
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search-heart display-2 text-muted opacity-25 mb-3"></i>
                        <h4 class="text-muted">No premium items matched your search filters.</h4>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>

</body>

</html>
