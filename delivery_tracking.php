<?php
require_once 'process/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$highlight_order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

// Backup helper for local payment returns where IPN/notify URL cannot reach localhost
if ($highlight_order_id && isset($_GET['pay_success']) && $_GET['pay_success'] == '1') {
    $update_stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
    $update_stmt->bind_param("ii", $highlight_order_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Send SMTP invoice mail
    try {
        require_once 'process/email.php';
        $invoice_body = getInvoiceHTML($highlight_order_id, $conn);
        
        $u_stmt = $conn->prepare("SELECT email, order_number FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $u_stmt->bind_param("i", $highlight_order_id);
        $u_stmt->execute();
        $res = $u_stmt->get_result()->fetch_assoc();
        $u_stmt->close();
        
        if ($res && isset($res['email'])) {
            sendEmail($res['email'], "Your Nova Street Invoice - " . $res['order_number'], $invoice_body);
        }
    } catch (Exception $e) {
        // ignore email errors
    }
}

// Fetch all orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$orders = [];
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

$active_orders = [];
$delivered_orders = [];
foreach ($orders as $order) {
    if ($order['delivery_status'] === 'delivered') {
        $delivered_orders[] = $order;
    } else {
        $active_orders[] = $order;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics & Tracking | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tracking-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #e5e5ea;
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .tracking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.06);
        }

        .tracking-card.highlight {
            border-color: var(--accent-color);
            box-shadow: 0 10px 40px rgba(191, 161, 95, 0.1);
        }

        .tracking-timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .tracking-timeline::before {
            content: '';
            position: absolute;
            left: 9px;
            top: 5px;
            bottom: 5px;
            width: 2px;
            background: #e5e5ea;
        }

        .timeline-step {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .timeline-step:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -26px;
            top: 4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #e5e5ea;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #e5e5ea;
            transition: var(--transition);
        }

        .timeline-step.active .timeline-dot {
            background: var(--accent-color);
            box-shadow: 0 0 0 2px var(--accent-color);
        }

        .timeline-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.2rem;
        }

        .timeline-desc {
            font-size: 0.85rem;
            color: #616166;
        }

        .star-rating i {
            color: #e5e5ea;
            cursor: pointer;
            transition: var(--transition);
        }

        .star-rating i.active {
            color: var(--accent-color);
        }

        .item-row-img {
            width: 45px;
            height: 55px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e5e5ea;
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
            <h1 class="display-5 fw-bold mb-2">LOGISTICS & TRACKING</h1>
            <p class="lead text-muted mb-5">Track your premium shipments and review past orders.</p>

            <?php if ($highlight_order_id && isset($_GET['pay_success']) && $_GET['pay_success'] == '1'): ?>
                <div class="alert alert-success border-0 rounded-4 p-4 mb-5 d-flex align-items-center gap-3" style="background: rgba(46, 213, 115, 0.08); color: #2ed573;">
                    <i class="bi bi-check-circle-fill fs-2"></i>
                    <div>
                        <h5 class="fw-bold mb-1">Payment Successful!</h5>
                        <p class="mb-0 small">Order #<?= htmlspecialchars($highlight_order_id) ?> has been authorized. We are preparing your shipment.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (count($orders) > 0): ?>
                
                <!-- Active Shipments -->
                <?php if (count($active_orders) > 0): ?>
                    <h3 class="fw-bold mb-4" style="letter-spacing: -0.5px;">Active Deliveries</h3>
                    <div class="row g-4 mb-5">
                        <?php foreach ($active_orders as $order): 
                            $order_id = $order['id'];
                            $items_stmt = $conn->prepare("SELECT oi.*, p.name AS title, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $items_stmt->bind_param("i", $order_id);
                            $items_stmt->execute();
                            $items_res = $items_stmt->get_result();
                            $items = [];
                            while ($row = $items_res->fetch_assoc()) {
                                $items[] = $row;
                            }
                            $items_stmt->close();
                            
                            $is_highlight = ($order_id === $highlight_order_id);
                        ?>
                            <div class="col-lg-6">
                                <div class="tracking-card <?= $is_highlight ? 'highlight' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
                                        <div>
                                            <h4 class="fw-bold mb-1">Order #<?= htmlspecialchars($order['order_number']) ?></h4>
                                            <small class="text-muted"><i class="bi bi-calendar3 me-2"></i><?= date('F j, Y', strtotime($order['created_at'])) ?></small>
                                        </div>
                                        <div class="text-end">
                                            <h4 class="fw-bold mb-1 text-accent-color">$<?= number_format($order['total_amount'], 2) ?></h4>
                                            <?php if ($order['payment_status'] === 'paid'): ?>
                                                <span class="badge rounded-pill bg-success px-3 py-2" style="font-size: 0.75rem;"><i class="bi bi-credit-card me-1"></i> PAID</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-warning text-dark px-3 py-2" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i> PENDING</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h6 class="small text-muted uppercase fw-bold mb-3"><i class="bi bi-bag me-2"></i> Items Ordered</h6>
                                        <div class="row g-2">
                                            <?php foreach ($items as $item): ?>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center bg-light p-2 rounded-3 border">
                                                        <img src="<?= htmlspecialchars(get_image_url($item['image_url'], $item['product_id'])) ?>" class="item-row-img me-3">
                                                        <div class="text-truncate">
                                                            <div class="fw-bold small text-truncate" title="<?= htmlspecialchars($item['title']) ?>"><?= htmlspecialchars($item['title']) ?></div>
                                                            <div class="text-muted small" style="font-size: 0.75rem;">Qty: <?= intval($item['quantity']) ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Tracking Stepper -->
                                    <div class="tracking-timeline">
                                        <?php
                                        $status = $order['delivery_status'];
                                        $is_processing = ($status === 'processing' || $status === 'shipped' || $status === 'delivered');
                                        $is_shipped = ($status === 'shipped' || $status === 'delivered');
                                        ?>
                                        <div class="timeline-step <?= $is_processing ? 'active' : '' ?>">
                                            <div class="timeline-dot"></div>
                                            <div class="timeline-title">Processing Order</div>
                                            <div class="timeline-desc">Verifying items, processing transaction, packaging.</div>
                                        </div>
                                        <div class="timeline-step <?= $is_shipped ? 'active' : '' ?>">
                                            <div class="timeline-dot"></div>
                                            <div class="timeline-title">Dispatched / Shipped</div>
                                            <div class="timeline-desc">Package handed over to our logistics delivery partner.</div>
                                        </div>
                                        <div class="timeline-step">
                                            <div class="timeline-dot"></div>
                                            <div class="timeline-title">Delivered</div>
                                            <div class="timeline-desc">Package arrived safely at your coordinates.</div>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-4 mb-4 border d-flex align-items-center gap-3" style="background: rgba(191, 161, 95, 0.04); border-color: rgba(191, 161, 95, 0.15) !important;">
                                        <i class="bi bi-truck fs-2 text-accent-color"></i>
                                        <div>
                                            <div class="fw-bold" style="color: var(--primary-color);">ETA: <strong><?= $order['estimated_delivery_days'] ?> Days</strong></div>
                                            <div class="text-muted small">Destination: <?= htmlspecialchars($order['delivery_district']) ?></div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="invoice.php?id=<?= $order['id'] ?>" class="btn btn-premium-outline btn-sm w-100 py-2 rounded-pill text-decoration-none text-center">
                                            <i class="bi bi-file-earmark-text me-2"></i>View Invoice
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Delivered Orders -->
                <?php if (count($delivered_orders) > 0): ?>
                    <h3 class="fw-bold mb-4" style="letter-spacing: -0.5px;">Past Orders</h3>
                    <div class="tracking-card p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small uppercase">
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Payment</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($delivered_orders as $order): 
                                        $order_id = $order['id'];
                                        $items_stmt = $conn->prepare("
                                            SELECT oi.*, p.name AS title, r.rating, r.comment 
                                            FROM order_items oi 
                                            JOIN products p ON oi.product_id = p.id 
                                            LEFT JOIN reviews r ON r.product_id = oi.product_id AND r.user_id = ? 
                                            WHERE oi.order_id = ?
                                        ");
                                        $items_stmt->bind_param("ii", $user_id, $order_id);
                                        $items_stmt->execute();
                                        $items_res = $items_stmt->get_result();
                                        $items = [];
                                        $item_titles = [];
                                        while ($row = $items_res->fetch_assoc()) {
                                            $items[] = $row;
                                            $item_titles[] = htmlspecialchars($row['title']) . ' (x' . intval($row['quantity']) . ')';
                                        }
                                        $items_stmt->close();
                                        $items_summary = implode(', ', $item_titles);
                                    ?>
                                        <tr>
                                            <td class="fw-bold">#<?= htmlspecialchars($order['order_number']) ?></td>
                                            <td class="text-muted small"><?= date('F j, Y', strtotime($order['created_at'])) ?></td>
                                            <td class="fw-bold text-accent-color">$<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <?php if ($order['payment_status'] === 'paid'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1" style="font-size: 0.7rem;">PAID</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1" style="font-size: 0.7rem;">PENDING</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-truncate" style="max-width: 250px;" title="<?= $items_summary ?>">
                                                <?= $items_summary ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-success px-2 py-1" style="font-size: 0.7rem;">Delivered</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <a href="invoice.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1">
                                                        Invoice
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-premium rounded-pill px-3 py-1 write-review-btn" 
                                                            data-order-id="<?= $order['id'] ?>" 
                                                            data-products='<?= json_encode($items, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                                        Review
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5 tracking-card">
                    <i class="bi bi-box-seam display-1 opacity-10"></i>
                    <h4 class="fw-bold mt-4">No Orders Found</h4>
                    <p class="text-muted">You have no active or completed orders in the system.</p>
                    <a href="products.php" class="btn btn-premium mt-3 rounded-pill">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-header border-bottom px-4 py-3 bg-light" style="border-top-left-radius: 24px; border-top-right-radius: 24px;">
                    <h5 class="modal-title fw-bold mb-0" id="reviewModalLabel">
                        <i class="bi bi-star-fill text-accent-color me-2"></i> TELEMETRY FEEDBACK
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
                    <div id="reviewProductsContainer">
                        <!-- Dynamic item forms loaded via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>

    <script>
        document.querySelectorAll('.write-review-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const products = JSON.parse(this.dataset.products);
                const container = document.getElementById('reviewProductsContainer');
                container.innerHTML = '';

                products.forEach(prod => {
                    const existingRating = prod.rating ? parseInt(prod.rating) : 0;
                    const existingComment = prod.comment ? prod.comment : '';
                    
                    const div = document.createElement('div');
                    div.className = "mb-4 pb-4 border-bottom last-border-0";
                    div.innerHTML = `
                        <h6 class="fw-bold mb-3">${prod.title}</h6>
                        <form class="review-submit-form" data-product-id="${prod.product_id}">
                            <div class="mb-3">
                                <label class="form-label text-muted small uppercase">Select Star Rating</label>
                                <div class="star-rating d-flex gap-2 fs-3 mb-2">
                                    <i class="bi ${existingRating >= 1 ? 'bi-star-fill active' : 'bi-star'}" data-value="1"></i>
                                    <i class="bi ${existingRating >= 2 ? 'bi-star-fill active' : 'bi-star'}" data-value="2"></i>
                                    <i class="bi ${existingRating >= 3 ? 'bi-star-fill active' : 'bi-star'}" data-value="3"></i>
                                    <i class="bi ${existingRating >= 4 ? 'bi-star-fill active' : 'bi-star'}" data-value="4"></i>
                                    <i class="bi ${existingRating >= 5 ? 'bi-star-fill active' : 'bi-star'}" data-value="5"></i>
                                </div>
                                <input type="hidden" name="rating" value="${existingRating}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small uppercase">Review Comment</label>
                                <textarea name="comment" class="form-control px-3 py-2" rows="3" placeholder="Share your feedback on this apparel..." style="border-radius: 12px;" required>${existingComment}</textarea>
                            </div>
                            <button type="submit" class="btn btn-premium btn-sm rounded-pill px-4">
                                ${existingRating > 0 ? 'Update Review' : 'Submit Review'}
                            </button>
                            <div class="review-status-msg small mt-2 d-none"></div>
                        </form>`;
                    
                    container.appendChild(div);

                    // Star interactivity
                    const stars = div.querySelectorAll('.star-rating i');
                    const input = div.querySelector('input[name="rating"]');

                    const setStars = (val) => {
                        stars.forEach(s => {
                            const sv = parseInt(s.dataset.value);
                            if (sv <= val) {
                                s.className = 'bi bi-star-fill active text-warning';
                            } else {
                                s.className = 'bi bi-star';
                            }
                        });
                    };

                    stars.forEach(star => {
                        star.addEventListener('mouseover', function() {
                            setStars(parseInt(this.dataset.value));
                        });
                        star.addEventListener('mouseout', function() {
                            setStars(parseInt(input.value) || 0);
                        });
                        star.addEventListener('click', function() {
                            const val = parseInt(this.dataset.value);
                            input.value = val;
                            setStars(val);
                        });
                    });
                });

                // Attach submit forms AJAX
                container.querySelectorAll('.review-submit-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const pId = this.dataset.productId;
                        const rating = this.querySelector('input[name="rating"]').value;
                        const comment = this.querySelector('textarea[name="comment"]').value;
                        const sBtn = this.querySelector('button[type="submit"]');
                        const status = this.querySelector('.review-status-msg');

                        if (!rating || parseInt(rating) < 1) {
                            status.className = "small mt-2 text-danger";
                            status.textContent = "Please select a star rating.";
                            status.classList.remove('d-none');
                            return;
                        }

                        sBtn.disabled = true;
                        sBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';

                        const data = new FormData();
                        data.append('product_id', pId);
                        data.append('rating', rating);
                        data.append('comment', comment);

                        fetch('process/save_review.php', {
                            method: 'POST',
                            body: data
                        })
                        .then(res => res.json())
                        .then(resData => {
                            status.className = "small mt-2 " + (resData.success ? "text-success" : "text-danger");
                            status.textContent = resData.message;
                            status.classList.remove('d-none');
                            
                            sBtn.disabled = false;
                            sBtn.textContent = 'Update Review';
                        })
                        .catch(err => {
                            console.error(err);
                            status.className = "small mt-2 text-danger";
                            status.textContent = "Connection error.";
                            status.classList.remove('d-none');
                            
                            sBtn.disabled = false;
                            sBtn.textContent = 'Submit Review';
                        });
                    });
                });

                const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
                modal.show();
            });
        });
    </script>
</body>

</html>
