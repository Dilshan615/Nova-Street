<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once 'db.php';

// 5. Parse incoming POST data (supports JSON and form urlencoded)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$action = isset($input['action']) ? trim($input['action']) : '';

if (empty($action)) {
    echo json_encode(["success" => false, "message" => "No action specified"]);
    exit();
}

// Enforce admin authentication for secure endpoints
$public_actions = ['signup', 'login', 'contact', 'newsletter', 'checkout', 'admin_login'];
if (!in_array($action, $public_actions)) {
    if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
        echo json_encode(["success" => false, "message" => "Unauthorized access. Please log in as administrator."]);
        exit();
    }
}

// 6. Handle action routing
switch ($action) {
    case 'signup':
        $first_name = isset($input['first_name']) ? trim($input['first_name']) : '';
        $last_name = isset($input['last_name']) ? trim($input['last_name']) : '';
        $email = isset($input['email']) ? trim($input['email']) : '';
        $contact = isset($input['contact']) ? trim($input['contact']) : '';
        $gender = isset($input['gender']) ? trim($input['gender']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';

        if (empty($first_name) || empty($last_name) || empty($email) || empty($contact) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Please fill in all required fields"]);
            exit();
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Email address is already registered"]);
            $stmt->close();
            exit();
        }
        $stmt->close();

        // Hash password and insert
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, contact, gender, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $first_name, $last_name, $email, $contact, $gender, $hashed_password);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Registration successful! You can now log in."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to create user account: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'login':
        $email = isset($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';

        if (empty($email) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Please fill in all fields"]);
            exit();
        }

        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['user_type'] = 'client';

                // Try to send welcome back email
                try {
                    require_once __DIR__ . '/email.php';
                    $welcome_subject = "Welcome Back to Nova Street, " . htmlspecialchars($user['first_name']) . "!";
                    $welcome_body = "
                        <div style='text-align: center;'>
                            <div style='font-size: 50px; margin-bottom: 20px;'>👋</div>
                            <h2 style='color: #0f0f11; margin-bottom: 10px; font-weight: 300;'>Welcome Back!</h2>
                            <p style='color: #2b2b2e; font-size: 15px;'>Hello " . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . ",</p>
                            <p style='color: #616166;'>You have successfully initialized a login sequence on your Nova Street account.</p>
                            <div style='background: #faf9f6; padding: 20px; border-radius: 12px; margin: 25px 0; border: 1px solid #e5e5ea;'>
                                <p style='margin: 0; color: #0f0f11;'>Account Session ID: <strong>#" . session_id() . "</strong></p>
                                <p style='margin: 5px 0 0; color: #bfa15f;'>Secure Access Authorized</p>
                            </div>
                            <p style='color: #616166; margin-bottom: 25px;'>If you did not authorize this login, please contact support immediately.</p>
                            <a href='http://" . $_SERVER['HTTP_HOST'] . "/project/Nova-Street/index.php' 
                               style='display: inline-block; background: #0f0f11; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;'>
                               Explore Store
                            </a>
                        </div>
                    ";
                    sendEmail($user['email'], $welcome_subject, $welcome_body);
                } catch (Exception $e) {
                    // ignore email errors
                }

                echo json_encode([
                    "success" => true,
                    "message" => "Welcome back, " . $user['first_name'] . "!",
                    "user" => [
                        "id" => $user['id'],
                        "first_name" => $user['first_name'],
                        "last_name" => $user['last_name'],
                        "email" => $user['email']
                    ]
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Incorrect password"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Email address not found"]);
        }
        $stmt->close();
        break;

    case 'contact':
        $name = isset($input['name']) ? trim($input['name']) : '';
        $email = isset($input['email']) ? trim($input['email']) : '';
        $subject = isset($input['subject']) ? trim($input['subject']) : '';
        $message = isset($input['message']) ? trim($input['message']) : '';

        if (empty($name) || empty($email) || empty($message)) {
            echo json_encode(["success" => false, "message" => "Please fill in all required fields"]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Message sent successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to save message: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'newsletter':
        $email = isset($input['email']) ? trim($input['email']) : '';

        if (empty($email)) {
            echo json_encode(["success" => false, "message" => "Email address is required"]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO newsletters (email) VALUES (?) ON DUPLICATE KEY UPDATE id=id");
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Subscribed successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to subscribe: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'checkout':
        $items = isset($input['items']) ? $input['items'] : [];
        $total = isset($input['total']) ? floatval($input['total']) : 0.0;

        if (empty($items)) {
            echo json_encode(["success" => false, "message" => "Shopping bag is empty"]);
            exit();
        }

        $order_number = "NS-" . rand(100000, 999999);
        $items_json = json_encode($items);

        $stmt = $conn->prepare("INSERT INTO orders (order_number, items_json, total_amount) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $order_number, $items_json, $total);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "order_number" => $order_number,
                "message" => "Order placed successfully!"
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to save order: " . $stmt->error]);
        }
        $stmt->close();
        break;

    // --- ADMIN ACTIONS ---

    case 'admin_login':
        $username = isset($input['username']) ? trim($input['username']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';

        if (empty($username) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Please fill in all fields"]);
            exit();
        }

        $stmt = $conn->prepare("SELECT id, username, email, password_hash FROM admins WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $admin = $res->fetch_assoc();
            if (password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                echo json_encode([
                    "success" => true,
                    "message" => "Authentication successful",
                    "admin" => [
                        "id" => $admin['id'],
                        "username" => $admin['username'],
                        "email" => $admin['email']
                    ]
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Incorrect password"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Admin account not found"]);
        }
        $stmt->close();
        break;

    case 'get_admin_stats':
        $stats = [];
        // Sales
        $res = $conn->query("SELECT SUM(total_amount) AS total FROM orders");
        $row = $res->fetch_assoc();
        $stats['total_sales'] = $row['total'] ? (float)$row['total'] : 0.0;

        // Orders
        $res = $conn->query("SELECT COUNT(*) AS count FROM orders");
        $row = $res->fetch_assoc();
        $stats['total_orders'] = (int)$row['count'];

        // Users
        $res = $conn->query("SELECT COUNT(*) AS count FROM users");
        $row = $res->fetch_assoc();
        $stats['total_users'] = (int)$row['count'];

        // Inquiries
        $res = $conn->query("SELECT COUNT(*) AS count FROM contacts");
        $row = $res->fetch_assoc();
        $stats['total_inquiries'] = (int)$row['count'];

        // Newsletters
        $res = $conn->query("SELECT COUNT(*) AS count FROM newsletters");
        $row = $res->fetch_assoc();
        $stats['total_newsletters'] = (int)$row['count'];

        echo json_encode(["success" => true, "stats" => $stats]);
        break;

    case 'get_products':
        $res = $conn->query("SELECT * FROM products ORDER BY id DESC");
        $products = [];
        while ($row = $res->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode(["success" => true, "products" => $products]);
        break;

    case 'add_product':
        $name = isset($input['name']) ? trim($input['name']) : '';
        $category_slug = isset($input['category_slug']) ? trim($input['category_slug']) : '';
        $price = isset($input['price']) ? floatval($input['price']) : 0.0;
        $description = isset($input['description']) ? trim($input['description']) : '';
        $image_url = isset($input['image_url']) ? trim($input['image_url']) : '';
        $color = isset($input['color']) ? trim($input['color']) : '';
        $size = isset($input['size']) ? trim($input['size']) : '';

        if (empty($name) || empty($category_slug) || $price <= 0 || empty($image_url)) {
            echo json_encode(["success" => false, "message" => "Please fill in all required fields"]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO products (name, category_slug, price, description, image_url, color, size) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssss", $name, $category_slug, $price, $description, $image_url, $color, $size);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Product added successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add product: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'edit_product':
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $name = isset($input['name']) ? trim($input['name']) : '';
        $category_slug = isset($input['category_slug']) ? trim($input['category_slug']) : '';
        $price = isset($input['price']) ? floatval($input['price']) : 0.0;
        $description = isset($input['description']) ? trim($input['description']) : '';
        $image_url = isset($input['image_url']) ? trim($input['image_url']) : '';
        $color = isset($input['color']) ? trim($input['color']) : '';
        $size = isset($input['size']) ? trim($input['size']) : '';

        if ($id <= 0 || empty($name) || empty($category_slug) || $price <= 0 || empty($image_url)) {
            echo json_encode(["success" => false, "message" => "Please fill in all required fields"]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE products SET name = ?, category_slug = ?, price = ?, description = ?, image_url = ?, color = ?, size = ? WHERE id = ?");
        $stmt->bind_param("ssdssssi", $name, $category_slug, $price, $description, $image_url, $color, $size, $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Product updated successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update product: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'delete_product':
        $id = isset($input['id']) ? intval($input['id']) : 0;
        if ($id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid product ID"]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Product deleted successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete product: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'get_categories':
        $res = $conn->query("SELECT * FROM categories ORDER BY id DESC");
        $categories = [];
        while ($row = $res->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode(["success" => true, "categories" => $categories]);
        break;

    case 'add_category':
        $name = isset($input['name']) ? trim($input['name']) : '';
        $slug = isset($input['slug']) ? trim($input['slug']) : '';
        $description = isset($input['description']) ? trim($input['description']) : '';
        $image_url = isset($input['image_url']) ? trim($input['image_url']) : '';

        if (empty($name) || empty($slug) || empty($image_url)) {
            echo json_encode(["success" => false, "message" => "Please fill in all required fields"]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $slug, $description, $image_url);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Category added successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add category: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'edit_category':
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $name = isset($input['name']) ? trim($input['name']) : '';
        $slug = isset($input['slug']) ? trim($input['slug']) : '';
        $description = isset($input['description']) ? trim($input['description']) : '';
        $image_url = isset($input['image_url']) ? trim($input['image_url']) : '';

        if ($id <= 0 || empty($name) || empty($slug) || empty($image_url)) {
            echo json_encode(["success" => false, "message" => "Please fill in all required fields"]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, image_url = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $slug, $description, $image_url, $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Category updated successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update category: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'delete_category':
        $id = isset($input['id']) ? intval($input['id']) : 0;
        if ($id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid category ID"]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Category deleted successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete category: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'get_users':
        $res = $conn->query("SELECT id, first_name, last_name, email, contact, gender, created_at FROM users ORDER BY id DESC");
        $users = [];
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(["success" => true, "users" => $users]);
        break;

    case 'get_orders':
        $res = $conn->query("SELECT * FROM orders ORDER BY id DESC");
        $orders = [];
        while ($row = $res->fetch_assoc()) {
            $orders[] = $row;
        }
        echo json_encode(["success" => true, "orders" => $orders]);
        break;

    case 'get_contacts':
        $res = $conn->query("SELECT * FROM contacts ORDER BY id DESC");
        $contacts = [];
        while ($row = $res->fetch_assoc()) {
            $contacts[] = $row;
        }
        echo json_encode(["success" => true, "contacts" => $contacts]);
        break;

    case 'get_newsletters':
        $res = $conn->query("SELECT * FROM newsletters ORDER BY id DESC");
        $newsletters = [];
        while ($row = $res->fetch_assoc()) {
            $newsletters[] = $row;
        }
        echo json_encode(["success" => true, "newsletters" => $newsletters]);
        break;

    default:
        echo json_encode(["success" => false, "message" => "Action not supported"]);
        break;
}

$conn->close();
