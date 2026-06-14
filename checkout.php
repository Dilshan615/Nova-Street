<?php
require_once 'process/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

$default_address = $user['address'] ?? '';
$default_district = $user['district'] ?? '';
$default_city = $user['city'] ?? '';
$default_phone = $user['contact'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- PayHere SDK -->
    <script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
    <style>
        .checkout-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #e5e5ea;
            margin-bottom: 2rem;
        }

        .checkout-title {
            text-transform: uppercase;
            font-size: 1.1rem;
            letter-spacing: 1.5px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkout-title span {
            background: var(--primary-color);
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .summary-sticky {
            position: sticky;
            top: 120px;
            background: #fff;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #e5e5ea;
        }

        .checkout-item-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.25rem;
        }

        .checkout-item-img {
            width: 50px;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e5e5ea;
        }

        .payment-option {
            border: 1px solid #e5e5ea;
            border-radius: 15px;
            padding: 1.25rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .payment-option:hover {
            border-color: var(--accent-color);
            background: rgba(191, 161, 95, 0.02);
        }

        .payment-option.active {
            border-color: var(--accent-color);
            background: rgba(191, 161, 95, 0.04);
        }
    </style>
</head>

<body>

    <?php 
    $active_page = 'cart';
    require_once 'includes/navbar.php'; 
    ?>

    <section class="py-5 mt-5">
        <div class="container py-5">
            <!-- Stepper Progress Tracker -->
            <div class="progress-steps mb-5">
                <div class="step-item">
                    <span class="step-number">1</span>
                    <span>Shopping Bag</span>
                </div>
                <div class="step-line active"></div>
                <div class="step-item active">
                    <span class="step-number">2</span>
                    <span>Checkout Details</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item">
                    <span class="step-number">3</span>
                    <span>Order Complete</span>
                </div>
            </div>

            <h1 class="display-6 fw-bold mb-5 text-center">SECURE CHECKOUT</h1>

            <form id="checkoutForm" method="POST" action="process/checkout.php">
                <div class="row g-5">
                    <!-- Left Column: Checkout steps -->
                    <div class="col-lg-7">
                        <!-- Step 1: Shipping details -->
                        <div class="checkout-card">
                            <h5 class="checkout-title"><span>1</span> Shipping Destination</h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted small uppercase">First Name</label>
                                    <input type="text" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars($user['first_name']) ?>" readonly disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted small uppercase">Last Name</label>
                                    <input type="text" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars($user['last_name']) ?>" readonly disabled>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="checkoutDistrict" class="form-label text-muted small uppercase">Select District</label>
                                <select name="district" id="checkoutDistrict" class="form-select rounded-pill px-4 py-2" required>
                                    <option value="">-- Choose District Sector --</option>
                                    <?php
                                    $shipping_res = $conn->query("SELECT * FROM shipping_rates ORDER BY rate_id ASC");
                                    if ($shipping_res) {
                                        while ($rate = $shipping_res->fetch_assoc()) {
                                            $selected = ($default_district === $rate['district']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($rate['district']) . "' data-shipping='" . floatval($rate['shipping_fee']) . "' data-days='" . intval($rate['delivery_days']) . "' $selected>" . htmlspecialchars($rate['district']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="checkoutCity" class="form-label text-muted small uppercase">City</label>
                                <input type="text" name="city" id="checkoutCity" class="form-control rounded-pill px-4 py-2" placeholder="e.g. Negombo" value="<?= htmlspecialchars($default_city) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="checkoutAddress" class="form-label text-muted small uppercase">Street Address</label>
                                <textarea name="address" id="checkoutAddress" class="form-control px-4 py-3" rows="3" placeholder="Enter street address and suite/apartment number..." style="border-radius: 20px;" required><?= htmlspecialchars($default_address) ?></textarea>
                            </div>
                        </div>

                        <!-- Step 2: Delivery method -->
                        <div class="checkout-card">
                            <h5 class="checkout-title"><span>2</span> Delivery Method</h5>
                            <div id="deliveryInfoBox" class="text-center py-4 bg-light rounded-3 text-muted">
                                Please select a shipping destination district to calculate logistics rates.
                            </div>
                        </div>

                        <!-- Step 3: Payment method -->
                        <div class="checkout-card">
                            <h5 class="checkout-title"><span>3</span> Payment Gateway Uplink</h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="payment-option active" id="payCard">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payMethodCard" value="card" checked>
                                        <label class="form-check-label fw-bold" for="payMethodCard">
                                            <i class="bi bi-credit-card me-2 fs-5" style="color: var(--accent-color);"></i> Card Payment
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="payment-option" id="payCOD">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payMethodCOD" value="cod">
                                        <label class="form-check-label fw-bold" for="payMethodCOD">
                                            <i class="bi bi-truck me-2 fs-5" style="color: var(--accent-color);"></i> Cash on Delivery
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Summary -->
                    <div class="col-lg-5">
                        <div class="summary-sticky">
                            <h4 class="fw-bold mb-4">Final Order Summary</h4>
                            
                            <div id="checkoutItemsList" class="mb-4 border-bottom pb-3" style="max-height: 260px; overflow-y: auto;">
                                <!-- Items populated via JS -->
                            </div>

                            <div class="d-flex justify-content-between mb-3 text-muted">
                                <span>Subtotal</span>
                                <span id="summarySubtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-muted">
                                <span>Logistics Shipping Fee</span>
                                <span id="summaryShipping">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-danger d-none" id="discountRow">
                                <span>Promo Discount (<span id="discountCodeLabel"></span>)</span>
                                <span>-$<span id="summaryDiscount">0.00</span></span>
                            </div>

                            <!-- Promo Code input -->
                            <div class="mb-4 pt-2">
                                <label class="form-label text-muted small uppercase">Promo / Voucher Code</label>
                                <div class="input-group">
                                    <input type="text" id="promoCodeInput" class="form-control rounded-start-pill px-4" placeholder="e.g. NOVA20" style="height: 46px;">
                                    <button class="btn btn-premium px-4 rounded-end-pill" type="button" id="promoApplyBtn">Apply</button>
                                </div>
                                <div id="promoMsg" class="small mt-2 d-none"></div>
                            </div>

                            <hr class="mb-4">
                            <div class="d-flex justify-content-between mb-4">
                                <h5 class="fw-bold">Total Payment</h5>
                                <h5 class="fw-bold text-accent-color" id="summaryTotal">$0.00</h5>
                            </div>

                            <!-- Hidden Fields to post cart payload -->
                            <input type="hidden" name="cart_data" id="hiddenCartData">
                            <input type="hidden" name="subtotal" id="hiddenSubtotal" value="0">
                            <input type="hidden" name="shipping_fee" id="hiddenShippingFee" value="0">
                            <input type="hidden" name="estimated_days" id="hiddenDays" value="3">
                            <input type="hidden" name="discount_amount" id="hiddenDiscountAmount" value="0">
                            <input type="hidden" name="promo_code" id="hiddenPromoCode" value="">

                            <button type="submit" class="btn btn-premium w-100 py-3 rounded-pill fs-6" id="submitCheckoutBtn">Authorize Payment</button>

                            <div class="mt-4 text-center">
                                <p class="small text-muted mb-0"><i class="bi bi-shield-lock me-2 text-accent-color"></i>AES-256 SSL Encryption Guaranteed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>

    <script>
        // Load cart and display summary
        let cart = JSON.parse(localStorage.getItem('novastreet-cart')) || [];
        
        if (cart.length === 0) {
            alert("Your shopping bag is empty! Redirecting back to cart.");
            window.location.href = 'cart.php';
        }

        // Populate hidden cart data
        document.getElementById('hiddenCartData').value = JSON.stringify(cart);

        let subtotal = 0;
        const itemsList = document.getElementById('checkoutItemsList');
        itemsList.innerHTML = cart.map(item => {
            let itemPrice = parseFloat(item.price.replace('$', ''));
            subtotal += itemPrice;
            return `
                <div class="checkout-item-preview">
                    <img src="${item.img}" class="checkout-item-img">
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold small text-truncate" style="max-width: 200px;">${item.name}</h6>
                        <span class="text-muted small">Size: M</span>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-accent-color small">${item.price}</span>
                    </div>
                </div>`;
        }).join('');

        document.getElementById('summarySubtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('hiddenSubtotal').value = subtotal;

        // Payment option style toggle
        document.getElementById('payMethodCard').addEventListener('change', function() {
            document.getElementById('payCard').classList.add('active');
            document.getElementById('payCOD').classList.remove('active');
        });

        document.getElementById('payMethodCOD').addEventListener('change', function() {
            document.getElementById('payCOD').classList.add('active');
            document.getElementById('payCard').classList.remove('active');
        });

        // Trigger change for visual states
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            });
        });

        // Shipping calculator
        let shippingFee = 0;
        let deliveryDays = 3;
        
        const recalculateTotal = () => {
            let discount = parseFloat(document.getElementById('hiddenDiscountAmount').value) || 0;
            let total = Math.max(0, subtotal + shippingFee - discount);
            document.getElementById('summaryTotal').textContent = `$${total.toFixed(2)}`;
        };

        const districtSelect = document.getElementById('checkoutDistrict');
        districtSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const infoBox = document.getElementById('deliveryInfoBox');
            if (opt.value) {
                shippingFee = parseFloat(opt.getAttribute('data-shipping'));
                deliveryDays = parseInt(opt.getAttribute('data-days'));
                
                infoBox.className = "p-4 border rounded-3 bg-light text-center";
                infoBox.innerHTML = `
                    <div class="badge bg-dark rounded-pill px-3 py-2 mb-2">Estimated Arrival: ${deliveryDays} Days</div>
                    <h6 class="fw-bold mb-1" style="color: var(--primary-color);">Shipping to ${opt.text} Sector</h6>
                    <p class="text-accent-color fw-bold mb-0">Delivery Fee: $${shippingFee.toFixed(2)}</p>
                `;

                document.getElementById('summaryShipping').textContent = `$${shippingFee.toFixed(2)}`;
                document.getElementById('hiddenShippingFee').value = shippingFee;
                document.getElementById('hiddenDays').value = deliveryDays;
            } else {
                shippingFee = 0;
                deliveryDays = 3;
                infoBox.className = "text-center py-4 bg-light rounded-3 text-muted";
                infoBox.innerHTML = "Please select a shipping destination district to calculate logistics rates.";
                document.getElementById('summaryShipping').textContent = "$0.00";
                document.getElementById('hiddenShippingFee').value = 0;
            }
            recalculateTotal();
        });

        // Initialize state
        if (districtSelect.value) {
            districtSelect.dispatchEvent(new Event('change'));
        } else {
            recalculateTotal();
        }

        // Apply promo logic
        let activePromo = null;
        document.getElementById('promoApplyBtn').addEventListener('click', async function() {
            const code = document.getElementById('promoCodeInput').value.trim();
            const msg = document.getElementById('promoMsg');

            if (!code) {
                msg.className = "small mt-2 text-danger";
                msg.textContent = "Please enter a code.";
                msg.classList.remove('d-none');
                return;
            }

            const btn = this;
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            try {
                const response = await fetch('ajax/validate_promo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ code: code })
                }).then(r => r.json());

                if (response.success) {
                    let discount = 0;
                    if (response.type === 'percentage') {
                        discount = subtotal * (response.value / 100);
                    } else if (response.type === 'fixed') {
                        discount = response.value;
                    } else if (response.type === 'free_shipping') {
                        discount = shippingFee;
                    }

                    activePromo = response;
                    document.getElementById('hiddenPromoCode').value = response.code;
                    document.getElementById('hiddenDiscountAmount').value = discount;
                    
                    document.getElementById('discountCodeLabel').textContent = response.code;
                    document.getElementById('summaryDiscount').textContent = discount.toFixed(2);
                    document.getElementById('discountRow').classList.remove('d-none');

                    msg.className = "small mt-2 text-success";
                    msg.textContent = response.message;
                } else {
                    activePromo = null;
                    document.getElementById('hiddenPromoCode').value = '';
                    document.getElementById('hiddenDiscountAmount').value = '0';
                    document.getElementById('discountRow').classList.add('d-none');

                    msg.className = "small mt-2 text-danger";
                    msg.textContent = response.message;
                }
                msg.classList.remove('d-none');
                recalculateTotal();
            } catch (e) {
                console.error(e);
                msg.className = "small mt-2 text-danger";
                msg.textContent = "Error communicating with server.";
                msg.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        });

        // Checkout submit intercept
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!districtSelect.value) {
                alert("Please select a shipping destination district.");
                return;
            }

            const submitBtn = document.getElementById('submitCheckoutBtn');
            const origText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';

            const formData = new FormData(this);
            fetch('process/checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.payment === 'payhere') {
                        // PayHere modal flow
                        payhere.onCompleted = function onCompleted(orderId) {
                            // Clear cart on success
                            localStorage.removeItem('novastreet-cart');
                            window.location.href = data.config.return_url;
                        };

                        payhere.onDismissed = function onDismissed() {
                            alert("Payment dismissed. Your order is registered as pending.");
                            localStorage.removeItem('novastreet-cart');
                            window.location.href = "delivery_tracking.php";
                        };

                        payhere.onError = function onError(error) {
                            alert("Payment gateway error: " + error);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = origText;
                        };

                        payhere.startPayment(data.config);
                    } else {
                        // COD flow - Order successful
                        localStorage.removeItem('novastreet-cart');
                        alert(data.message || "Order placed successfully!");
                        window.location.href = "delivery_tracking.php?order_id=" + data.order_id;
                    }
                } else {
                    alert(data.message || "Checkout failed. Please try again.");
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origText;
                }
            })
            .catch(err => {
                console.error(err);
                alert("Server error processing request.");
                submitBtn.disabled = false;
                submitBtn.innerHTML = origText;
            });
        });
    </script>

</body>

</html>
