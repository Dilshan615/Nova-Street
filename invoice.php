<?php
require_once 'process/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
$order_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$order_id) {
    echo "<h3>Order ID is required.</h3>";
    exit();
}

// Fetch order details
$stmt = $conn->prepare("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "<h3>Order not found.</h3>";
    exit();
}

// Authorization check
if ($order['user_id'] != $user_id && !$is_admin) {
    echo "<h3>Access Denied. You are not authorized to view this invoice.</h3>";
    exit();
}

// Fetch order items
$items_stmt = $conn->prepare("SELECT oi.*, p.name AS title, p.image_url, p.color, p.size FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$res = $items_stmt->get_result();
$order_items = [];
while ($row = $res->fetch_assoc()) {
    $order_items[] = $row;
}
$items_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($order['order_number']) ?> | Nova Street</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .invoice-card {
            background: #fff;
            padding: 3rem;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #e5e5ea;
            position: relative;
        }

        .invoice-watermark {
            position: absolute;
            top: 2rem;
            right: 2rem;
            opacity: 0.03;
            pointer-events: none;
            user-select: none;
            font-size: 8rem;
            font-weight: 900;
            letter-spacing: 5px;
            font-family: 'Outfit', sans-serif;
            color: var(--primary-color);
        }

        .invoice-logo {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -1.5px;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .invoice-logo span {
            color: var(--accent-color);
        }

        .item-thumbnail {
            width: 40px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e5e5ea;
        }

        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
            }
            .navbar, footer, .d-print-none {
                display: none !important;
            }
            .invoice-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .invoice-watermark {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <?php 
    $active_page = 'cart';
    if (!isset($_GET['embed'])) {
        require_once 'includes/navbar.php'; 
    }
    ?>

    <div class="container py-5 mt-5">
        
        <!-- Action Buttons -->
        <div class="d-flex <?= isset($_GET['embed']) ? 'justify-content-end' : 'justify-content-between' ?> align-items-center mb-4 d-print-none">
            <?php if (!isset($_GET['embed'])): ?>
                <a href="delivery_tracking.php" class="btn btn-premium-outline rounded-pill btn-sm px-4">
                    <i class="bi bi-arrow-left me-2"></i>Back to Tracking
                </a>
            <?php endif; ?>
            <div class="d-flex gap-2">
                <button onclick="downloadPDF()" class="btn btn-premium-outline rounded-pill btn-sm px-4">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
                </button>
                <button onclick="window.print()" class="btn btn-premium rounded-pill btn-sm px-4">
                    <i class="bi bi-printer me-2"></i>Print Invoice
                </button>
            </div>
        </div>

        <!-- Invoice Body -->
        <div class="invoice-card" id="invoiceCard">
            <div class="invoice-watermark">INVOICE</div>

            <div class="row g-4 align-items-start mb-5">
                <!-- Shop info -->
                <div class="col-md-6">
                    <div class="invoice-logo">NOVA <span>STREET.</span></div>
                    <p class="text-muted mb-0 small">
                        Premium Fashion House<br>
                        Galle Road, Colombo 03, Sector Sri Lanka<br>
                        Contact: customer-care@novastreet.com
                    </p>
                </div>

                <!-- Invoice metadata -->
                <div class="col-md-6 text-md-end">
                    <h2 class="fw-bold mb-1" style="letter-spacing: -0.5px;">INVOICE</h2>
                    <div class="text-muted small mb-1">Invoice Number: <strong>#<?= htmlspecialchars($order['order_number']) ?></strong></div>
                    <div class="text-muted small mb-1">Date: <?= date('F j, Y, g:i A', strtotime($order['created_at'])) ?></div>
                    <div class="mt-2">
                        <?php if ($order['payment_status'] === 'paid'): ?>
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> PAID</span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill">PENDING</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Billed and Shipped columns -->
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <h6 class="text-muted small uppercase fw-bold mb-2" style="letter-spacing: 0.5px;">Billed To</h6>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></h5>
                    <p class="text-muted mb-0 small">
                        Email: <?= htmlspecialchars($order['email']) ?><br>
                        Client Reference ID: #NS-USR-<?= str_pad($order['user_id'], 4, '0', STR_PAD_LEFT) ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted small uppercase fw-bold mb-2" style="letter-spacing: 0.5px;">Shipping Destination</h6>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($order['delivery_district']) ?> Sector</h5>
                    <p class="text-muted mb-0 small">
                        Address: <?= nl2br(htmlspecialchars($order['delivery_address'] ?? '')) ?><br>
                        Method: Standard Courier Service<br>
                        Logistics Status: <span class="badge bg-dark rounded-pill px-2 py-0 ms-1" style="font-size: 0.65rem;"><?= htmlspecialchars(strtoupper($order['delivery_status'])) ?></span>
                    </p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead>
                        <tr class="table-light text-muted small uppercase">
                            <th scope="col" class="py-3 px-3">Item Description</th>
                            <th scope="col" class="py-3 text-center" style="width: 15%;">Unit Price</th>
                            <th scope="col" class="py-3 text-center" style="width: 10%;">Qty</th>
                            <th scope="col" class="py-3 text-end px-3" style="width: 20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): 
                            $item_total = $item['unit_price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td class="py-3 px-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars(get_image_url($item['image_url'], $item['product_id'])) ?>" class="item-thumbnail me-3 d-print-none">
                                        <div>
                                            <h6 class="fw-bold mb-0 small"><?= htmlspecialchars($item['title']) ?></h6>
                                            <small class="text-muted">Size: <?= htmlspecialchars($item['size'] ?: 'M') ?> | Color: <?= htmlspecialchars($item['color'] ?: 'Natural') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center py-3">$<?= number_format($item['unit_price'], 2) ?></td>
                                <td class="text-center py-3"><?= intval($item['quantity']) ?></td>
                                <td class="text-end text-accent-color fw-bold py-3 px-3">$<?= number_format($item_total, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Total calculations -->
            <div class="row justify-content-end">
                <div class="col-md-5 text-end">
                    <?php
                    $discount = floatval($order['discount_amount']);
                    // Back-calculate subtotal
                    $calc_subtotal = 0;
                    foreach ($order_items as $item) {
                        $calc_subtotal += ($item['unit_price'] * $item['quantity']);
                    }
                    $shipping = floatval($order['total_amount']) - $calc_subtotal + $discount;
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold">$<?= number_format($calc_subtotal, 2) ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-danger">Discount (<?= htmlspecialchars($order['promo_code']) ?>):</span>
                            <span class="text-danger fw-bold">-$<?= number_format($discount, 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping & Logistics Fee:</span>
                        <span class="fw-bold">$<?= number_format($shipping, 2) ?></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Total Payment:</h5>
                        <h4 class="fw-bold text-accent-color mb-0" style="font-size: 1.6rem;">$<?= number_format($order['total_amount'], 2) ?></h4>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-top text-center text-muted small">
                <p class="mb-1">Thank you for shopping at Nova Street. This is a computer generated invoice and requires no physical signature.</p>
                <p class="mb-0">Powered by Nova Street Logistics &copy; <?= date('Y') ?></p>
            </div>
        </div>
    </div>

    <?php 
    if (!isset($_GET['embed'])) {
        require_once 'includes/footer.php'; 
    }
    ?>

    <!-- html2pdf SDK -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.getElementById('invoiceCard');
            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'Nova_Street_Invoice_#<?= $order['order_number'] ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>

</body>

</html>
