<?php
require_once 'db.php';

$merchant_id         = $_POST['merchant_id'] ?? '';
$order_id            = $_POST['order_id'] ?? '';
$payhere_amount      = $_POST['payhere_amount'] ?? '';
$payhere_currency    = $_POST['payhere_currency'] ?? '';
$status_code         = $_POST['status_code'] ?? '';
$md5sig              = $_POST['md5sig'] ?? '';
$payment_id          = $_POST['payment_id'] ?? '';

// Debug Log
$log = date('Y-m-d H:i:s') . " - ID: $order_id, Status: $status_code, PayID: $payment_id\n";
file_put_contents('payhere_log.txt', $log, FILE_APPEND);

// Fetch PayHere secret from settings
$merchant_secret = get_setting('payhere_secret', '');

$local_md5sig = strtoupper(
    md5(
        $merchant_id .
        $order_id .
        $payhere_amount .
        $payhere_currency .
        $status_code .
        strtoupper(md5($merchant_secret))
    )
);

if (($local_md5sig === $md5sig) && ($status_code == 2)) {
    require_once 'email.php';

    // Check if it's a Nova Street Order (ORD-)
    if (strpos($order_id, 'ORD-') === 0) {
        $real_id = str_replace('ORD-', '', $order_id);
        
        try {
            // Update Order to paid using MySQLi
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', payhere_payment_id = ? WHERE id = ?");
            $stmt->bind_param("si", $payment_id, $real_id);
            $stmt->execute();
            $stmt->close();

            // Fetch user email
            $u_stmt = $conn->prepare("SELECT u.email, o.order_number FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
            $u_stmt->bind_param("i", $real_id);
            $u_stmt->execute();
            $res = $u_stmt->get_result()->fetch_assoc();
            $u_stmt->close();

            if ($res && isset($res['email'])) {
                $user_email = $res['email'];
                $order_number = $res['order_number'];
                $invoice_body = getInvoiceHTML($real_id, $conn);
                sendEmail($user_email, "Your Nova Street Invoice - " . $order_number, $invoice_body);
            }
            
            echo "Success";
        } catch (Exception $e) {
            error_log("Error updating order/sending email in notify: " . $e->getMessage());
            echo "Error: " . $e->getMessage();
        }
    }
} else {
    echo "Invalid signature or status code";
}
?>
