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
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: logout.php");
    exit();
}

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Fetch order stats
$orders_stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$order_stats = $orders_stmt->get_result()->fetch_assoc();
$orders_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #e5e5ea;
            height: 100%;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--accent-color);
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: var(--accent-color);
        }
    </style>
</head>

<body>

    <?php 
    $active_page = 'cart';
    require_once 'includes/navbar.php'; 
    ?>

    <div class="container py-5 mt-5">
        <div class="row g-5 justify-content-center py-5">
            <!-- Sidebar Card -->
            <div class="col-lg-4">
                <div class="profile-card text-center d-flex flex-column align-items-center">
                    <div class="avatar-circle">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    
                    <h3 class="fw-bold mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                    <p class="text-muted mb-4 small"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email']) ?></p>
                    
                    <hr class="w-100 my-4">
                    
                    <div class="w-100 text-start px-2 mb-4">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted"><i class="bi bi-calendar3 me-2"></i>Member Since:</span>
                            <span class="fw-bold"><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-bag-check me-2"></i>Total Orders:</span>
                            <span class="text-accent-color fw-bold"><?= intval($order_stats['total_orders'] ?? 0) ?> Orders</span>
                        </div>
                    </div>
                    
                    <div class="mt-auto w-100">
                        <a href="delivery_tracking.php" class="btn btn-premium-outline btn-sm w-100 py-3 rounded-pill text-decoration-none text-center">
                            <i class="bi bi-box-seam me-2"></i>Track Orders
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Settings Form -->
            <div class="col-lg-6">
                <div class="profile-card">
                    <div class="mb-4">
                        <h2 class="fw-bold mb-1">My Account Settings</h2>
                        <p class="text-muted small">Update your personal preferences and delivery addresses.</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 rounded-4 p-3 mb-4" style="background: rgba(46, 213, 115, 0.08); color: #2ed573;">
                            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 rounded-4 p-3 mb-4" style="background: rgba(235, 94, 85, 0.08); color: #eb5e55;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="process/profile.php">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="profileFirstName" class="form-label text-muted small uppercase">First Name</label>
                                <input type="text" name="first_name" id="profileFirstName" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profileLastName" class="form-label text-muted small uppercase">Last Name</label>
                                <input type="text" name="last_name" id="profileLastName" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="profileEmail" class="form-label text-muted small uppercase">Email Address</label>
                            <input type="email" id="profileEmail" class="form-control rounded-pill px-4 py-2 bg-light text-muted" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled title="Email address cannot be changed">
                        </div>

                        <div class="mb-3">
                            <label for="profileAddress" class="form-label text-muted small uppercase">Default Shipping Address</label>
                            <textarea name="address" id="profileAddress" class="form-control px-4 py-3" rows="3" placeholder="Enter default street coordinates..." style="border-radius: 20px;" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="profileDistrict" class="form-label text-muted small uppercase">District</label>
                                <select name="district" id="profileDistrict" class="form-select rounded-pill px-4 py-2" required>
                                    <option value="">-- Choose District --</option>
                                    <?php
                                    $shipping_res = $conn->query("SELECT * FROM shipping_rates ORDER BY rate_id ASC");
                                    if ($shipping_res) {
                                        while ($rate = $shipping_res->fetch_assoc()) {
                                            $selected = ($user['district'] === $rate['district']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($rate['district']) . "' $selected>" . htmlspecialchars($rate['district']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="profileCity" class="form-label text-muted small uppercase">City</label>
                                <input type="text" name="city" id="profileCity" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="e.g. Negombo" required>
                            </div>
                        </div>

                        <h5 class="fw-bold mt-5 mb-3 border-top pt-4">Security Settings</h5>
                        <p class="text-muted small mb-4">Fill these fields only if you wish to change your password.</p>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="profilePassword" class="form-label text-muted small uppercase">New Password</label>
                                <input type="password" name="password" id="profilePassword" class="form-control rounded-pill px-4 py-2" placeholder="••••••••">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profileConfirmPassword" class="form-label text-muted small uppercase">Confirm Password</label>
                                <input type="password" name="confirm_password" id="profileConfirmPassword" class="form-control rounded-pill px-4 py-2" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-premium py-3 rounded-pill">
                                <i class="bi bi-check2-circle me-2"></i>Save Account Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const district = document.getElementById('profileDistrict');
            const city = document.getElementById('profileCity');
            const address = document.getElementById('profileAddress');

            const toggleInputs = () => {
                const hasDistrict = district.value.trim() !== "";
                city.disabled = !hasDistrict;
                address.disabled = !hasDistrict || !city.value.trim();
            };

            district.addEventListener('change', toggleInputs);
            city.addEventListener('input', toggleInputs);
            toggleInputs();
        });
    </script>
</body>

</html>
