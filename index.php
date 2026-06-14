<?php
require_once 'process/db.php';

// Fetch categories
$categories_res = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories = [];
if ($categories_res) {
    while ($row = $categories_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch featured products (first 4 products)
$products_res = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
$featured_products = [];
if ($products_res) {
    while ($row = $products_res->fetch_assoc()) {
        $featured_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Street | Elegance in Every Stitch</title>
    <meta name="description"
        content="Premium clothing store for the modern aesthetic. Discover our new summer collection and luxury accessories.">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php 
    $active_page = 'home';
    require_once 'includes/navbar.php'; 
    ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content" data-aos="fade-up">
                    <h1>Discover the <span>Elegance</span> of Modern Fashion</h1>
                    <p>Experience the perfect blend of minimalist design and premium craftsmanship. Our new collection
                        is designed for those who value style and comfort.</p>
                    <a href="products.php" class="btn btn-premium">Explore Collection</a>
                </div>
                <div class="col-lg-6">
                    <div class="hero-images-grid">
                        <div class="hero-img-item item-large" data-aos="zoom-in">
                            <img src="assets/img/hero.png" alt="Featured Fashion">
                        </div>
                        <div class="hero-img-item item-small" data-aos="zoom-in" data-aos-delay="200">
                            <img src="assets/img/accessories.png" alt="Accessories">
                        </div>
                        <div class="hero-img-item item-small" data-aos="zoom-in" data-aos-delay="400">
                            <img src="assets/img/blog.png" alt="Style">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="py-5" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                    <div class="about-img-container position-relative">
                        <img src="assets/img/blog.png" class="w-100 rounded-4 shadow-lg" alt="About Nova Street">
                        <div
                            class="about-experience bg-white p-4 rounded-4 shadow-sm position-absolute bottom-0 end-0 m-4 d-none d-md-block">
                            <h3 class="fw-bold mb-0 text-dark">12+</h3>
                            <p class="small text-muted mb-0">Years of Style</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5" data-aos="fade-left">
                    <h6 class="text-uppercase text-muted fw-bold mb-3">Our Story</h6>
                    <h2 class="display-5 mb-4">Redefining Elegance for the Modern Individual</h2>
                    <p class="text-muted mb-4">Founded in 2014, Nova Street has always been more than just a clothing
                        brand. We
                        are a community of dreamers, creators, and style enthusiasts who believe that fashion should be
                        as comfortable as it is sophisticated.</p>
                    <p class="text-muted mb-4">Our journey began with a simple mission: to create high-quality, timeless
                        pieces that stand the test of time. Every stitch is a promise of quality, and every design is a
                        celebration of individuality.</p>
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-accent-light p-2 rounded-circle me-3"
                                    style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-shield-check text-accent-color h4 mb-0"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Quality First</h6>
                                    <p class="small text-muted mb-0">Premium Fabrics</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-accent-light p-2 rounded-circle me-3"
                                    style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-globe text-accent-color h4 mb-0"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Sustainable</h6>
                                    <p class="small text-muted mb-0">Eco-friendly Approach</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="about.php" class="btn btn-premium">Learn More About Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories / Featured Products -->
    <section class="py-5" id="shop">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end section-title">
                <div>
                    <h6 class="text-uppercase text-muted fw-bold mb-2">Our Collections</h6>
                    <h2 class="display-5">Shop By Category</h2>
                </div>
                <ul class="nav category-tabs d-none d-md-flex" id="categoryTab">
                    <li class="nav-item"><a class="nav-link active" href="products.php">All Products</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li class="nav-item"><a class="nav-link" href="products.php?category=<?= htmlspecialchars($cat['slug']); ?>"><?= htmlspecialchars($cat['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="row">
                <?php if (empty($featured_products)): ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">No products available in store.</h4>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_products as $index => $prod): ?>
                        <?php $delay = $index * 100; ?>
                        <!-- Product Card -->
                        <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="<?= $delay; ?>">
                            <div class="product-card">
                                <div class="product-img-wrapper">
                                    <img src="<?= htmlspecialchars($prod['image_url']); ?>" alt="<?= htmlspecialchars($prod['name']); ?>">
                                    <div class="product-actions">
                                        <button class="action-btn fav-btn" title="Add to Wishlist"><i
                                                class="bi bi-heart"></i></button>
                                        <button class="action-btn cart-btn" title="Add to Cart"><i
                                                class="bi bi-bag-plus"></i></button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h5><?= htmlspecialchars($prod['name']); ?></h5>
                                    <p><?= htmlspecialchars($prod['color'] ?: 'Natural Swatch'); ?></p>
                                    <h6 class="fw-bold">$<?= number_format($prod['price'], 2); ?></h6>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Latest Stories / Blog -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h2 class="display-5">Fashion Journal</h2>
                <a href="products.php" class="btn border-dark rounded-pill px-4">View All Stories</a>
            </div>
            <div class="featured-story" data-aos="fade-up">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <img src="assets/img/blog.png" class="img-fluid story-img w-100" alt="Journal Image">
                    </div>
                    <div class="col-lg-5 ps-lg-5 mt-4 mt-lg-0">
                        <span class="text-muted d-block mb-3">Trending • Aug 15, 2025</span>
                        <h3 class="mb-4">The Evolution of Minimalist Fashion in 2026</h3>
                        <p class="text-muted mb-4">Discover how clean lines and neutral palettes are redefining luxury
                        in the modern era. We explore the balance between comfort and sophistication.</p>
                        <a href="products.php" class="btn btn-premium">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials / Highlights -->
    <section class="py-5 text-center">
        <div class="container">
            <h2 class="display-6 mb-5">Customer Voice</h2>
            <div class="row justify-content-center">
                <div class="col-md-6" data-aos="zoom-in">
                    <div class="p-4 bg-white rounded-4 shadow-sm">
                        <div class="mb-3 text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <h5 class="mb-3">"Exceptional quality and fit. The minimalist aesthetic is exactly what I was
                            looking for."</h5>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="bg-secondary rounded-circle me-3" style="width: 50px; height: 50px;"></div>
                            <div class="text-start">
                                <h6 class="mb-0">Sarah J.</h6>
                                <p class="small text-muted mb-0">Fashion Consultant</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section class="py-5" id="contact">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-aos="fade-right">
                    <h6 class="text-uppercase text-muted fw-bold mb-3">Get In Touch</h6>
                    <h2 class="display-5 mb-4">We'd Love to Hear from You</h2>
                    <p class="text-muted mb-5">Have a question about our collections or need help with an order? Our
                        team is here to provide you with the best experience.</p>

                    <div class="contact-info-card d-flex align-items-center mb-4 p-4 rounded-4 bg-white shadow-sm">
                        <div class="bg-accent-light p-3 rounded-circle me-4">
                            <i class="bi bi-geo-alt text-accent-color h4 mb-0"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold">Our Boutique</h6>
                            <p class="small text-muted mb-0">123 Fashion Street, Colombo 07, Sri Lanka</p>
                        </div>
                    </div>

                    <div class="contact-info-card d-flex align-items-center mb-4 p-4 rounded-4 bg-white shadow-sm">
                        <div class="bg-accent-light p-3 rounded-circle me-4">
                            <i class="bi bi-envelope text-accent-color h4 mb-0"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold">Email Us</h6>
                            <p class="small text-muted mb-0">hello@novastreetfashion.com</p>
                        </div>
                    </div>

                    <div class="contact-info-card d-flex align-items-center p-4 rounded-4 bg-white shadow-sm">
                        <div class="bg-accent-light p-3 rounded-circle me-4">
                            <i class="bi bi-telephone text-accent-color h4 mb-0"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold">Call Us</h6>
                            <p class="small text-muted mb-0">+94 11 234 5678</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7" data-aos="fade-left">
                    <div class="contact-form-wrapper p-5 rounded-5 bg-white shadow-lg">
                        <form id="contactForm">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control border-0 bg-light" id="name"
                                            placeholder="Your Name" required>
                                        <label for="name">Your Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control border-0 bg-light" id="email"
                                            placeholder="Your Email" required>
                                        <label for="email">Your Email</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control border-0 bg-light" id="subject"
                                            placeholder="Subject">
                                        <label for="subject">Subject</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control border-0 bg-light"
                                            placeholder="Leave a message here" id="message" style="height: 150px"
                                            required></textarea>
                                        <label for="message">Message</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-premium w-100 py-3">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter-section" data-aos="fade-up">
        <div class="container">
            <h2 class="display-5 mb-3">Join the Nova Street Club</h2>
            <p class="mb-5 opacity-75">Get exclusive access to new releases, style guides, and members-only offers.</p>
            <form class="newsletter-form">
                <input type="email" id="newsletterEmail" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-premium">Subscribe</button>
            </form>
            <p class="mt-4 small opacity-50">By subscribing, you agree to our Privacy Policy.</p>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
</body>

</html>
