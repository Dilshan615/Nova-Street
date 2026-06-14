<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .wishlist-page-card {
            background: #fff;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            transition: var(--transition);
            border: none;
        }

        .wishlist-page-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .wishlist-img-box {
            height: 300px;
            position: relative;
        }

        .wishlist-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-wish-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff4757;
            transition: var(--transition);
        }

        .remove-wish-btn:hover {
            background: #ff4757;
            color: #fff;
        }
    </style>
</head>

<body>

    <?php 
    $active_page = 'wishlist';
    require_once 'includes/navbar.php'; 
    ?>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php" id="nav-home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php" id="nav-categories">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php" id="nav-products">All Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php" id="nav-about">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact" id="nav-contact">Contact</a></li>
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
                <a href="login.php" class="btn p-0" title="Account"><i class="bi bi-person fs-4"></i></a>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <section class="py-5 mt-5">
        <div class="container py-5">
            <!-- Welcoming Style Board Banner -->
            <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3" data-aos="fade-up">
                <div>
                    <h1 class="display-5 fw-bold mb-0">My Favorites Board</h1>
                    <p class="text-muted mt-2 mb-0" id="wishlistCountText">Your curated style choices will appear here.</p>
                </div>
                <button class="share-board-btn" id="shareWishlistBtn">
                    <i class="bi bi-share"></i>
                    <span>Share Style Board</span>
                </button>
            </div>

            <div id="wishlistPageGrid" class="row g-4" data-aos="fade-up" data-aos-delay="100">
                <!-- Wishlist items will be injected by script.js -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-accent-color" role="status"></div>
                </div>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
</body>

</html>
