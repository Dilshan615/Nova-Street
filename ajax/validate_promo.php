<?php
header('Content-Type: application/json');
require_once '../process/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User session is not active.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$code = strtoupper(trim($_POST['code'] ?? ''));

if ($code === '') {
    echo json_encode(['success' => false, 'message' => 'Promo code cannot be empty.']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM promo_codes WHERE code = ? AND status = 'active'");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $promo = $res->fetch_assoc();
    $stmt->close();

    if ($promo) {
        echo json_encode([
            'success' => true,
            'message' => 'Promo code successfully applied!',
            'code' => $promo['code'],
            'type' => $promo['type'],
            'value' => floatval($promo['value'])
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or inactive promo code reference.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
