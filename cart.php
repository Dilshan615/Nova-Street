<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Bag | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-page-item {
            background: #fff;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
            transition: var(--transition);
        }

        .cart-page-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .cart-item-image {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border-radius: 15px;
        }

        .summary-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 100px;
        }
    </style>
</head>

<body>

    <?php 
    $active_page = 'cart';
    require_once 'includes/navbar.php'; 
    ?>

    <!-- Page Content -->
    <section class="py-5 mt-5">
        <div class="container py-5">
            <!-- Stepper Progress Tracker -->
            <div class="progress-steps" data-aos="fade-down">
                <div class="step-item active">
                    <span class="step-number">1</span>
                    <span>Shopping Bag</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item">
                    <span class="step-number">2</span>
                    <span>Checkout Details</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item">
                    <span class="step-number">3</span>
                    <span>Order Complete</span>
                </div>
            </div>

            <h1 class="display-5 fw-bold mb-5 text-center">Your Shopping Bag</h1>

            <div class="row g-5">
                <!-- Cart Items List -->
                <div class="col-lg-8" data-aos="fade-right">
                    <div id="cartPageList">
                        <!-- Items will be injected by script.js -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-accent-color" role="status"></div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="summary-card">
                        <h4 class="fw-bold mb-4">Order Summary</h4>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Subtotal</span>
                            <span id="summarySubtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Estimated Shipping</span>
                            <span class="text-success fw-bold">FREE</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 text-muted">
                            <span>Tax (2%)</span>
                            <span id="summaryTax">$0.00</span>
                        </div>
                        
                        <!-- Promo Code Accordion/Collapse -->
                        <div class="mb-4">
                            <button class="btn btn-link p-0 text-decoration-none text-dark d-flex align-items-center justify-content-between w-100" type="button" data-bs-toggle="collapse" data-bs-target="#promoCollapse" aria-expanded="false" aria-controls="promoCollapse">
                                <span class="small fw-bold text-uppercase" style="letter-spacing: 1px;">Add Promo Code</span>
                                <i class="bi bi-chevron-down small"></i>
                            </button>
                            <div class="collapse mt-2" id="promoCollapse">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm border bg-light" id="promoInput" placeholder="Enter code">
                                    <button class="btn btn-premium btn-sm" type="button" id="promoApplyBtn">Apply</button>
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4">
                        <div class="d-flex justify-content-between mb-4">
                            <h5 class="fw-bold">Total</h5>
                            <h5 class="fw-bold text-accent-color" id="summaryTotal">$0.00</h5>
                        </div>
                        <button class="btn btn-premium w-100 py-3 rounded-pill" id="checkoutBtn">Proceed to Checkout</button>

                        <div class="mt-4 text-center">
                            <p class="small text-muted mb-0"><i class="bi bi-shield-check me-2 text-accent-color"></i>Secure SSL Checkout Guaranteed</p>
                            <div class="d-flex justify-content-center gap-2 mt-3 opacity-75">
                                <i class="bi bi-credit-card-2-front fs-5" title="Card Payment"></i>
                                <i class="bi bi-lock fs-5" title="Encrypted Connection"></i>
                                <i class="bi bi-patch-check fs-5" title="Verified Safe"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
</body>

</html>
