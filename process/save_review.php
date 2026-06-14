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
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = trim($_POST['comment'] ?? '');

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please select a rating between 1 and 5 stars.']);
    exit();
}

try {
    // 1. Verify that the user has actually purchased this product and payment is paid
    $stmt_purch = $conn->prepare("
        SELECT 1 FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND oi.product_id = ? AND o.payment_status = 'paid'
        LIMIT 1
    ");
    $stmt_purch->bind_param("ii", $user_id, $product_id);
    $stmt_purch->execute();
    $purchased = (bool)$stmt_purch->get_result()->fetch_assoc();
    $stmt_purch->close();

    if (!$purchased) {
        echo json_encode(['success' => false, 'message' => 'You can only review products that you have purchased and paid.']);
        exit();
    }

    // 2. Check if a review already exists for this product by this user
    $stmt_rev = $conn->prepare("SELECT 1 FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
    $stmt_rev->bind_param("ii", $user_id, $product_id);
    $stmt_rev->execute();
    $already_reviewed = (bool)$stmt_rev->get_result()->fetch_assoc();
    $stmt_rev->close();

    if ($already_reviewed) {
        // If already reviewed, update the review comment and rating
        $stmt_update = $conn->prepare("UPDATE reviews SET rating = ?, comment = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
        $stmt_update->bind_param("isii", $rating, $comment, $user_id, $product_id);
        if ($stmt_update->execute()) {
            echo json_encode(['success' => true, 'message' => 'Your review has been updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update review.']);
        }
        $stmt_update->close();
    } else {
        // If not reviewed, insert a new review record
        $stmt_insert = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("iiis", $product_id, $user_id, $rating, $comment);
        if ($stmt_insert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Your review has been submitted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review.']);
        }
        $stmt_insert->close();
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
