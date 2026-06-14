<?php
require_once 'process/db.php';
$categories_res = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories = [];
if ($categories_res) {
    while ($row = $categories_res->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php 
    $active_page = 'categories';
    require_once 'includes/navbar.php'; 
    ?>

    <!-- Page Header -->
    <section class="py-5 mt-5">
        <div class="container py-5">
            <h1 class="display-3 fw-bold mb-4" data-aos="fade-up">Shop Categories</h1>
            <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Find your perfect style from our curated
                collections.</p>
        </div>
    </section>

    <!-- Categories Grid -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-4">
                <?php if (empty($categories)): ?>
                    <div class="col-12 text-center py-5">
                        <h3 class="text-muted">No categories available.</h3>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $index => $cat): ?>
                        <?php 
                        // Alternating large (6/12) and smaller (4/12) cards
                        $col_class = ($index < 2) ? 'col-md-6' : 'col-md-4';
                        $height = ($index < 2) ? '500px' : '400px';
                        $delay = $index * 100;
                        ?>
                        <div class="<?= $col_class; ?>" data-aos="fade-up" data-aos-delay="<?= $delay; ?>">
                            <div class="category-card position-relative overflow-hidden rounded-4" style="height: <?= $height; ?>;">
                                <img src="<?= htmlspecialchars($cat['image_url']); ?>" class="w-100 h-100 object-fit-cover transition"
                                    alt="<?= htmlspecialchars($cat['name']); ?>">
                                <div class="position-absolute bottom-0 start-0 p-4 w-100 bg-gradient-dark">
                                    <h2 class="text-white <?= ($index < 2) ? 'h1' : 'h3'; ?> mb-3"><?= htmlspecialchars($cat['name']); ?></h2>
                                    <?php if (!empty($cat['description'])): ?>
                                        <p class="text-white opacity-75 mb-4"><?= htmlspecialchars($cat['description']); ?></p>
                                    <?php endif; ?>
                                    <a href="products.php?category=<?= htmlspecialchars($cat['slug']); ?>" class="btn <?= ($index < 2) ? 'btn-light' : 'btn-sm btn-light'; ?> rounded-pill px-4">Browse Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
</body>

</html>
