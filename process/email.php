<?php
/**
 * NOVA STREET - CENTRAL EMAIL HUB
 * ---------------------------------------------------------
 * Direct instantiation of PHPMailer or use of mail() elsewhere is strictly forbidden.
 * ---------------------------------------------------------
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

if (!function_exists('wrapInPremiumTemplate')) {
    function wrapInPremiumTemplate($content, $subject = "Notification")
    {
        $year = date('Y');
        $store_name = get_setting('store_name', 'Nova Street');
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                .email-wrapper {
                    background-color: #faf9f6;
                    padding: 40px 20px;
                    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                }
                .email-container {
                    max-width: 650px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(15,15,17,0.05);
                    border: 1px solid #e5e5ea;
                }
                .header {
                    background-color: #0f0f11;
                    padding: 40px 30px;
                    text-align: center;
                    border-bottom: 3px solid #bfa15f;
                }
                .header h1 {
                    color: #ffffff;
                    margin: 0;
                    font-size: 26px;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    font-weight: 300;
                }
                .header h1 span {
                    color: #bfa15f;
                    font-weight: 700;
                }
                .content {
                    padding: 40px 35px;
                    color: #2b2b2e;
                    line-height: 1.6;
                }
                .footer {
                    background-color: #0f0f11;
                    padding: 25px;
                    text-align: center;
                    font-size: 13px;
                    color: #a1a1a6;
                    border-top: 1px solid #1c1c1e;
                }
                .footer p { margin: 5px 0; }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='email-container'>
                    <div class='header'>
                        <h1>NOVA <span>STREET</span></h1>
                    </div>
                    <div class='content'>
                        $content
                    </div>
                    <div class='footer'>
                        <p>&copy; $year $store_name. All Rights Reserved.</p>
                        <p>This is an automated notification. Please contact customer care for assistance.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}

if (!function_exists('sendEmail')) {
    function sendEmail($to, $subject, $body)
    {
        $mail = new PHPMailer(true);

        try {
            $smtp_username = get_setting('smtp_username', 'dilshan0763126293@gmail.com');
            $smtp_password = get_setting('smtp_password', 'heqi qcfe bstk ijez');
            $store_name = get_setting('store_name', 'Nova Street');

            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_username;
            $mail->Password   = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->Timeout    = 30;

            // SSL Options for XAMPP stability
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom($smtp_username, $store_name);
            $mail->addAddress($to);
            $mail->addReplyTo($smtp_username, $store_name . ' Support');

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;

            // Wrap body in premium template
            $mail->Body    = wrapInPremiumTemplate($body, $subject);
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}

if (!function_exists('getInvoiceHTML')) {
    function getInvoiceHTML($order_id, $conn)
    {
        // Fetch Order
        $stmt = $conn->prepare("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        if ($conn instanceof mysqli) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            // PDO
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();
        }

        if (!$order) {
            return "Order not found.";
        }

        // Fetch Order Items
        $items = [];
        if ($conn instanceof mysqli) {
            $items_stmt = $conn->prepare("SELECT oi.*, p.name AS title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $items_stmt->bind_param("i", $order_id);
            $items_stmt->execute();
            $res = $items_stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $items[] = $row;
            }
            $items_stmt->close();
        } else {
            // PDO
            $items_stmt = $conn->prepare("SELECT oi.*, p.name AS title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $items_stmt->execute([$order_id]);
            $items = $items_stmt->fetchAll();
        }

        // Calculate subtotal
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['unit_price'] * $item['quantity']);
        }

        $shipping_fee = floatval($order['total_amount']) - $subtotal + floatval($order['discount_amount']);

        ob_start();
        ?>
        <div style="font-family: Arial, sans-serif; color: #2b2b2e; max-width: 600px; margin: auto; border: 1px solid #e5e5ea; background-color: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.02);">
            <div style="text-align: center; border-bottom: 2px solid #bfa15f; padding-bottom: 20px; margin-bottom: 20px;">
                <h1 style="color: #0f0f11; margin: 0; font-weight: 300; letter-spacing: 1.5px;">NOVA <span style="color: #bfa15f; font-weight: 700;">STREET</span></h1>
                <p style="color: #a1a1a6; margin: 5px 0; font-size: 13px;">Premium Fashion House Invoice</p>
            </div>

            <div style="margin-bottom: 30px;">
                <div style="float: left; width: 50%;">
                    <p style="font-size: 10px; color: #a1a1a6; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">Billed To</p>
                    <h3 style="margin: 5px 0; color: #0f0f11; font-size: 16px; font-weight: 600;"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></h3>
                    <p style="font-size: 13px; margin: 0; color: #616166; line-height: 1.4;"><?php echo nl2br(htmlspecialchars($order['delivery_address'] ?? '')); ?></p>
                </div>
                <div style="float: right; width: 50%; text-align: right;">
                    <p style="font-size: 10px; color: #a1a1a6; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">Order Details</p>
                    <h3 style="margin: 5px 0; color: #bfa15f; font-size: 16px; font-weight: 700;">#<?php echo htmlspecialchars($order['order_number'] ?? $order_id); ?></h3>
                    <p style="font-size: 13px; margin: 0; color: #616166;"><?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                </div>
                <div style="clear: both;"></div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <thead>
                    <tr style="background: #faf9f6;">
                        <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e5ea; color: #0f0f11; font-weight: 600; font-size: 13px;">Item Name</th>
                        <th style="text-align: center; padding: 12px; border-bottom: 1px solid #e5e5ea; color: #0f0f11; font-weight: 600; font-size: 13px;">Unit Price</th>
                        <th style="text-align: center; padding: 12px; border-bottom: 1px solid #e5e5ea; color: #0f0f11; font-weight: 600; font-size: 13px;">Qty</th>
                        <th style="text-align: right; padding: 12px; border-bottom: 1px solid #e5e5ea; color: #0f0f11; font-weight: 600; font-size: 13px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): 
                        $item_total = $item['unit_price'] * $item['quantity'];
                    ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #e5e5ea; color: #2b2b2e; font-size: 13px;"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td style="text-align: center; padding: 12px; border-bottom: 1px solid #e5e5ea; color: #2b2b2e; font-size: 13px;">$<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td style="text-align: center; padding: 12px; border-bottom: 1px solid #e5e5ea; color: #2b2b2e; font-size: 13px;"><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right; padding: 12px; border-bottom: 1px solid #e5e5ea; color: #bfa15f; font-weight: bold; font-size: 13px;">$<?php echo number_format($item_total, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="text-align: right; font-size: 14px;">
                <p style="margin: 5px 0; color: #616166;">Subtotal: <span style="color: #0f0f11; font-weight: bold;">$<?php echo number_format($subtotal, 2); ?></span></p>
                <?php if (floatval($order['discount_amount']) > 0): ?>
                    <p style="margin: 5px 0; color: #ef4444;">Discount (<?php echo htmlspecialchars($order['promo_code'] ?? ''); ?>): <span style="color: #ef4444; font-weight: bold;">-$<?php echo number_format($order['discount_amount'], 2); ?></span></p>
                <?php endif; ?>
                <p style="margin: 5px 0; color: #616166;">Shipping Fee (<?php echo htmlspecialchars($order['delivery_district'] ?? 'Standard'); ?>): <span style="color: #0f0f11; font-weight: bold;">$<?php echo number_format($shipping_fee, 2); ?></span></p>
                <h2 style="margin: 20px 0 0; color: #bfa15f; font-weight: 700; font-size: 20px;">Total Payment: $<?php echo number_format($order['total_amount'], 2); ?></h2>
            </div>

            <div style="margin-top: 40px; text-align: center; color: #a1a1a6; font-size: 11px; border-top: 1px solid #e5e5ea; padding-top: 20px;">
                <p>Thank you for choosing Nova Street! We hope to serve you again soon.</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if (!function_exists('sendOrderStatusEmail')) {
    function sendOrderStatusEmail($order_id, $status, $conn)
    {
        // Fetch Order and User Details
        $order = null;
        $stmt = $conn->prepare("SELECT o.id AS order_id, o.order_number, u.email, u.first_name, o.total_amount 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?");
        if ($conn instanceof mysqli) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            // PDO
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();
        }
        
        if (!$order) return false;

        $status_title = "";
        $status_desc = "";
        $icon = "";

        switch ($status) {
            case 'processing':
                $status_title = "Order Accepted";
                $status_desc = "Great news! We have accepted your order and are now preparing your items for delivery.";
                $icon = "✨";
                break;
            case 'shipped':
                $status_title = "On the Way!";
                $status_desc = "Your package has been handed over to the courier and is on its way to your destination.";
                $icon = "🚚";
                break;
            case 'delivered':
                $status_title = "Delivered!";
                $status_desc = "Your order has been successfully delivered. We hope you enjoy your new wardrobe additions!";
                $icon = "🛍️";
                break;
            default:
                return false;
        }

        $msg = "
            <div style='text-align: center;'>
                <div style='font-size: 50px; margin-bottom: 20px;'>$icon</div>
                <h2 style='color: #0f0f11; margin-bottom: 10px; font-weight: 300; letter-spacing: 1px;'>$status_title</h2>
                <p style='color: #616166; font-size: 15px;'>Hello " . htmlspecialchars($order['first_name']) . ",</p>
                <p style='color: #2b2b2e;'>$status_desc</p>
                <div style='background: #faf9f6; padding: 20px; border-radius: 12px; margin: 25px 0; border: 1px solid #e5e5ea;'>
                    <p style='margin: 0; font-weight: bold; color: #0f0f11;'>Order Number: #" . htmlspecialchars($order['order_number']) . "</p>
                    <p style='margin: 5px 0 0; color: #bfa15f; font-weight: 700;'>Total: $" . number_format($order['total_amount'], 2) . "</p>
                </div>
                <a href='http://" . $_SERVER['HTTP_HOST'] . "/project/Nova-Street/delivery_tracking.php?order_id=" . $order_id . "' 
                   style='display: inline-block; background: #0f0f11; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;'>
                   Track Your Package
                </a>
            </div>
        ";

        return sendEmail($order['email'], "Order Update: $status_title (#" . ($order['order_number'] ?? $order_id) . ")", $msg);
    }
}
