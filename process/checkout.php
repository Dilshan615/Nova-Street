<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? 'card';
$district = $_POST['district'] ?? '';
$city = trim($_POST['city'] ?? '');
$address = trim($_POST['address'] ?? '');
$shipping_fee = floatval($_POST['shipping_fee'] ?? 0);
$subtotal = floatval($_POST['subtotal'] ?? 0);
$estimated_days = intval($_POST['estimated_days'] ?? 3);
$discount_amount = floatval($_POST['discount_amount'] ?? 0);
$promo_code = trim($_POST['promo_code'] ?? '');
$cart_data = $_POST['cart_data'] ?? '[]';

$total_amount = max(0, $subtotal + $shipping_fee - $discount_amount);
$full_delivery_address = $address . ", " . $city;

if (empty($address) || empty($district) || empty($city)) {
    echo json_encode(['success' => false, 'message' => 'Shipping district, city, and address are required.']);
    exit();
}

$cart_items = json_decode($cart_data, true);
if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Your shopping bag is empty.']);
    exit();
}

// Group cart items by name to calculate quantities
$grouped_cart = [];
foreach ($cart_items as $item) {
    $name = $item['name'];
    if (isset($grouped_cart[$name])) {
        $grouped_cart[$name]['quantity'] += 1;
    } else {
        $grouped_cart[$name] = [
            'name' => $name,
            'quantity' => 1
        ];
    }
}

try {
    // Fetch user details
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    // Save default address preferences in user profile if they weren't set yet
    if (empty($user['address']) || empty($user['district'])) {
        $save_addr = $conn->prepare("UPDATE users SET address = ?, district = ?, city = ? WHERE id = ?");
        $save_addr->bind_param("sssi", $address, $district, $city, $user_id);
        $save_addr->execute();
        $save_addr->close();
    }

    $conn->begin_transaction();

    // 1. Create Order
    $order_number = "NS-" . rand(100000, 999999);
    $items_json = json_encode($cart_items); // for backup compatibility

    $stmt = $conn->prepare("INSERT INTO orders (order_number, items_json, user_id, total_amount, discount_amount, promo_code, payment_status, delivery_status, estimated_delivery_days, delivery_district, delivery_address) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'processing', ?, ?, ?)");
    
    $promo_val = ($promo_code !== '') ? $promo_code : null;
    $stmt->bind_param("ssidssiss", $order_number, $items_json, $user_id, $total_amount, $discount_amount, $promo_val, $estimated_days, $district, $full_delivery_address);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    // 2. Add Order Items and update inventory
    $item_insert = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $stock_update = $conn->prepare("UPDATE products SET qty_in_stock = GREATEST(0, qty_in_stock - ?) WHERE id = ?");

    foreach ($grouped_cart as $item) {
        // Find product details securely from database
        $prod_stmt = $conn->prepare("SELECT id, price FROM products WHERE name = ?");
        $prod_stmt->bind_param("s", $item['name']);
        $prod_stmt->execute();
        $product = $prod_stmt->get_result()->fetch_assoc();
        $prod_stmt->close();

        if ($product) {
            $p_id = $product['id'];
            $p_price = $product['price'];
            $p_qty = $item['quantity'];

            $item_insert->bind_param("iiii", $order_id, $p_id, $p_qty, $p_price);
            $item_insert->execute();

            $stock_update->bind_param("ii", $p_qty, $p_id);
            $stock_update->execute();
        }
    }

    $item_insert->close();
    $stock_update->close();

    $conn->commit();

    // Handle Payment Gateways
    if ($payment_method === 'card') {
        $merchant_id = get_setting('payhere_merchant_id', '1222410');
        $merchant_secret = get_setting('payhere_secret', 'NDI0MTczNTE5NzQyNTk5OTQ4ODczNzA3NzI1ODc3NjQyNDcyMzM=');
        $currency = "LKR"; // PayHere sandbox default

        $payhere_order_id = "ORD-" . $order_id;
        // Convert USD price to LKR for demonstration payment gateway lookup
        $lkr_total = $total_amount * 300.00; 

        $hash = strtoupper(
            md5(
                $merchant_id . 
                $payhere_order_id . 
                number_format($lkr_total, 2, '.', '') . 
                $currency . 
                strtoupper(md5($merchant_secret))
            )
        );

        echo json_encode([
            'success' => true,
            'payment' => 'payhere',
            'config' => [
                'sandbox' => true,
                'merchant_id' => $merchant_id,
                'return_url' => "http://" . $_SERVER['HTTP_HOST'] . "/project/Nova-Street/delivery_tracking.php?order_id=" . $order_id . "&pay_success=1",
                'cancel_url' => "http://" . $_SERVER['HTTP_HOST'] . "/project/Nova-Street/checkout.php",
                'notify_url' => "http://" . $_SERVER['HTTP_HOST'] . "/project/Nova-Street/process/payhere_notify.php",
                'order_id' => $payhere_order_id,
                'items' => 'Nova Street Order #' . $order_number,
                'amount' => number_format($lkr_total, 2, '.', ''),
                'currency' => $currency,
                'hash' => $hash,
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'phone' => $default_phone ?: '+94 11 234 5678',
                'address' => $address,
                'city' => $city,
                'country' => 'Sri Lanka'
            ]
        ]);
    } else {
        // Cash on Delivery
        require_once 'email.php';
        $invoice_body = getInvoiceHTML($order_id, $conn);
        sendEmail($user['email'], "Order Confirmation - " . $order_number, $invoice_body);

        echo json_encode([
            'success' => true,
            'payment' => 'cod',
            'order_id' => $order_id,
            'message' => 'Order placed successfully!'
        ]);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
