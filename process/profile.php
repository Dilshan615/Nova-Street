<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($first_name) || empty($last_name)) {
        $_SESSION['error'] = "First name and last name are required.";
        header("Location: ../profile.php");
        exit();
    } elseif (!empty($city) && empty($district)) {
        $_SESSION['error'] = "You cannot enter a City without choosing a District.";
        header("Location: ../profile.php");
        exit();
    } elseif (!empty($address) && (empty($district) || empty($city))) {
        $_SESSION['error'] = "You cannot enter a Shipping Address without providing both District and City.";
        header("Location: ../profile.php");
        exit();
    } else {
        try {
            // Update details
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, address = ?, district = ?, city = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $first_name, $last_name, $address, $district, $city, $user_id);
            $stmt->execute();
            $stmt->close();
            
            $_SESSION['first_name'] = $first_name; // update active session name
            $_SESSION['success'] = "Profile details updated successfully!";
            
            // Password update if provided
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    $_SESSION['error'] = "Passwords do not match.";
                    $_SESSION['success'] = "";
                } elseif (strlen($password) < 6) {
                    $_SESSION['error'] = "Password must be at least 6 characters long.";
                    $_SESSION['success'] = "";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $pwd_stmt->bind_param("si", $hashed_password, $user_id);
                    $pwd_stmt->execute();
                    $pwd_stmt->close();
                    $_SESSION['success'] = "Profile details and password updated successfully!";
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        }
    }
}
header("Location: ../profile.php");
exit();
?>
