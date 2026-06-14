<?php
$host = "localhost";
$db_user = "root";
$db_pass = "Manja123#";
$db_name = "nova_street";

// Try connecting to localhost
$conn = @new mysqli($host, $db_user, $db_pass);

if ($conn->connect_error) {
    // Try 127.0.0.1 in case localhost lookup/IPv6 DNS resolution fails
    $conn = @new mysqli("127.0.0.1", $db_user, $db_pass);
    if ($conn->connect_error) {
        // Return JSON error if called via AJAX process
        if (basename($_SERVER['PHP_SELF']) === 'process.php') {
            echo json_encode(["success" => false, "message" => "Database connection failed. Please ensure MySQL is running in XAMPP."]);
            exit();
        } else {
            die("Database connection failed. Please ensure MySQL is running in XAMPP. Error: " . $conn->connect_error);
        }
    }
}

// Automatically create database and tables if they do not exist
$conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
if (!$conn->select_db($db_name)) {
    if (basename($_SERVER['PHP_SELF']) === 'process.php') {
        echo json_encode(["success" => false, "message" => "Failed to select database: " . $conn->error]);
        exit();
    } else {
        die("Failed to select database: " . $conn->error);
    }
}

// Initialize tables
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contact VARCHAR(20) NOT NULL,
    gender VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS newsletters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(30) UNIQUE NOT NULL,
    items_json TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255) NULL,
    image_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_slug VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT NULL,
    image_url VARCHAR(255) NOT NULL,
    color VARCHAR(50) NULL,
    size VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");


// Seed default data if empty
$res = $conn->query("SELECT id FROM admins LIMIT 1");
if ($res && $res->num_rows == 0) {
    $admin_user = 'admin';
    $admin_email = 'admin@gmail.com';
    $admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $admin_user, $admin_email, $admin_pass);
        $stmt->execute();
        $stmt->close();
    }
}

$res = $conn->query("SELECT id FROM categories LIMIT 1");
if ($res && $res->num_rows == 0) {
    $conn->query("INSERT INTO categories (name, slug, description, image_url) VALUES
        ('Women\'s Collection', 'women', 'Elegant dresses, tops, and more.', 'assets/img/hero.png'),
        ('Men\'s Collection', 'men', 'Sophisticated suits and casual wear.', 'assets/img/men.png'),
        ('Kids World', 'kids', 'Playful designs for little ones.', 'assets/img/summer.png'),
        ('Luxury Accents', 'accessories', 'Premium details to complete your look.', 'assets/img/accessories.png'),
        ('Summer Essence', 'summer', 'Minimalist styling for hot days.', 'assets/img/summer.png'),
        ('Arctic Comfort', 'winter', 'Premium warmth and insulation.', 'assets/img/winter.png')");
}

$res = $conn->query("SELECT id FROM products LIMIT 1");
if ($res && $res->num_rows == 0) {
    $conn->query("INSERT INTO products (name, category_slug, price, description, image_url, color, size) VALUES
        ('Linen Summer Dress', 'women', 120.00, 'Minimalist light cotton wear.', 'assets/img/summer.png', 'Light White', 'S, M, L'),
        ('Slim Fit Blazer', 'men', 250.00, 'Professional charcoal wool.', 'assets/img/men.png', 'Midnight Blue', 'M, L, XL'),
        ('Silk Minimalist Top', 'women', 85.00, 'Smooth Mulberry crepe silk top.', 'assets/img/hero.png', 'Cream', 'XS, S, M'),
        ('Wool Blend Coat', 'winter', 400.00, 'Premium cashmere and wool insulation.', 'assets/img/winter.png', 'Charcoal', 'M, L'),
        ('Leather Crossbody Bag', 'accessories', 180.00, 'Hand-crafted tan leather bag.', 'assets/img/accessories.png', 'Tan', 'One Size'),
        ('Cotton Relaxed Pants', 'men', 95.00, 'Relaxed fit organic cotton trousers.', 'assets/img/summer.png', 'Olive', 'S, M, L')");
}

// --- Dynamic Schema Migrations for Ported Features ---

// Helper function to safely add columns
if (!function_exists('db_add_column_if_not_exists')) {
    function db_add_column_if_not_exists($conn, $table, $column, $definition) {
        $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($res && $res->num_rows == 0) {
            $conn->query("ALTER TABLE `$table` ADD `$column` $definition");
        }
    }
}

// 1. Alter products to add qty_in_stock
db_add_column_if_not_exists($conn, 'products', 'qty_in_stock', 'INT DEFAULT 10');

// 2. Alter users to add address, district, city
db_add_column_if_not_exists($conn, 'users', 'address', 'TEXT DEFAULT NULL');
db_add_column_if_not_exists($conn, 'users', 'district', 'VARCHAR(100) DEFAULT NULL');
db_add_column_if_not_exists($conn, 'users', 'city', 'VARCHAR(100) DEFAULT NULL');

// 3. Alter orders to add user_id, discount_amount, promo_code, payment_status, delivery_status, estimated_delivery_days, delivery_district, delivery_address, payhere_payment_id
db_add_column_if_not_exists($conn, 'orders', 'user_id', 'INT NULL');
db_add_column_if_not_exists($conn, 'orders', 'discount_amount', 'DECIMAL(10,2) DEFAULT 0.00');
db_add_column_if_not_exists($conn, 'orders', 'promo_code', 'VARCHAR(50) DEFAULT NULL');
db_add_column_if_not_exists($conn, 'orders', 'payment_status', "ENUM('pending', 'paid') DEFAULT 'pending'");
db_add_column_if_not_exists($conn, 'orders', 'delivery_status', "ENUM('processing', 'shipped', 'delivered') DEFAULT 'processing'");
db_add_column_if_not_exists($conn, 'orders', 'estimated_delivery_days', 'INT DEFAULT 3');
db_add_column_if_not_exists($conn, 'orders', 'delivery_district', 'VARCHAR(100) DEFAULT NULL');
db_add_column_if_not_exists($conn, 'orders', 'delivery_address', 'TEXT DEFAULT NULL');
db_add_column_if_not_exists($conn, 'orders', 'payhere_payment_id', 'VARCHAR(100) DEFAULT NULL');

// 4. Create new tables
$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS promo_codes (
    promo_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('percentage', 'fixed', 'free_shipping') NOT NULL DEFAULT 'percentage',
    value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS shipping_rates (
    rate_id INT AUTO_INCREMENT PRIMARY KEY,
    district VARCHAR(100) UNIQUE NOT NULL,
    shipping_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    delivery_days INT NOT NULL DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (product_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 5. Seed initial settings, promo codes, shipping rates if empty
$res = $conn->query("SELECT promo_id FROM promo_codes LIMIT 1");
if ($res && $res->num_rows == 0) {
    $conn->query("INSERT IGNORE INTO promo_codes (code, type, value, status) VALUES 
        ('NOVA10', 'percentage', 10.00, 'active'),
        ('NOVA20', 'percentage', 20.00, 'active'),
        ('NOVA30', 'percentage', 30.00, 'active'),
        ('FREESHIP', 'free_shipping', 0.00, 'active')");
}

$res = $conn->query("SELECT rate_id FROM shipping_rates LIMIT 1");
if ($res && $res->num_rows == 0) {
    $conn->query("INSERT IGNORE INTO shipping_rates (district, shipping_fee, delivery_days) VALUES 
        ('Colombo', 200.00, 1),
        ('Kandy', 400.00, 2),
        ('Galle', 350.00, 2),
        ('Jaffna', 600.00, 4),
        ('Other', 500.00, 5)");
}

$res = $conn->query("SELECT setting_key FROM settings LIMIT 1");
if ($res && $res->num_rows == 0) {
    $conn->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
        ('store_name', 'Nova Street'),
        ('contact_email', 'support@novastreet.com'),
        ('contact_phone', '+94 11 234 5678'),
        ('store_address', 'No. 88, Galle Road, Colombo 03'),
        ('payhere_merchant_id', '1222410'),
        ('payhere_secret', 'NDI0MTczNTE5NzQyNTk5OTQ4ODczNzA3NzI1ODc3NjQyNDcyMzM='),
        ('smtp_username', 'dilshan0763126293@gmail.com'),
        ('smtp_password', 'heqi qcfe bstk ijez')");
}

// 6. Initialize PDO Connection for Ported Pages
try {
    $conn_pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $conn_pdo = new PDO("mysql:host=127.0.0.1;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $conn_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $conn_pdo = null;
    }
}

// 7. Define Dynamic Settings Helper Function
if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        global $conn;
        static $settings_cache = null;
        if ($settings_cache === null) {
            $settings_cache = [];
            $res = $conn->query("SELECT setting_key, setting_value FROM settings");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $settings_cache[$row['setting_key']] = $row['setting_value'];
                }
            }
        }
        return $settings_cache[$key] ?? $default;
    }
}

// 8. Define Dynamic Image URL Helper Function
if (!function_exists('get_image_url')) {
    function get_image_url($image_url, $product_id = null) {
        if (empty($image_url)) {
            return 'assets/img/hero.png';
        }
        if (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
            return $image_url;
        }
        if (strpos($image_url, '/') === 0) {
            return $image_url;
        }
        // Relative path, resolve dynamically depending on subdirectory context
        $current_script = $_SERVER['SCRIPT_NAME'] ?? '';
        $is_sub_dir = (strpos($current_script, '/admin/') !== false || strpos($current_script, '/ajax/') !== false || strpos($current_script, '/process/') !== false);
        if ($is_sub_dir) {
            return '../' . $image_url;
        } else {
            return $image_url;
        }
    }
}

