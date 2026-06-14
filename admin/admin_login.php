<?php
session_start();
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Nova Street</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #f7f7f8;
            color: #0f0f11;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-family: 'Outfit', sans-serif;
        }

        .admin-login-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 30px;
            padding: 3.5rem;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .admin-login-card:hover {
            border-color: rgba(197, 160, 89, 0.4);
        }

        .btn-admin-login {
            background-color: #c5a059;
            color: #000;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 1rem;
            border-radius: 50px;
            border: none;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-admin-login:hover {
            background-color: #b38e47;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(197, 160, 89, 0.15);
        }

        .admin-brand {
            font-size: 2.2rem;
            font-weight: 300;
            letter-spacing: 2px;
            color: #0f0f11;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .admin-brand span {
            color: #c5a059;
            font-weight: 600;
        }

        .form-control {
            background-color: rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 50px;
            padding: 1rem 1.5rem;
            color: #0f0f11;
        }

        .form-control:focus {
            background-color: rgba(0, 0, 0, 0.04);
            border-color: #c5a059;
            box-shadow: none;
            color: #0f0f11;
        }

        .form-label {
            color: rgba(15, 15, 17, 0.6);
            font-size: 0.85rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-left: 1rem;
        }

        .admin-sub {
            color: rgba(15, 15, 17, 0.4);
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 2.5rem;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>

    <div class="admin-login-card">
        <h1 class="admin-brand">Nova<span>Street</span>.</h1>
        <p class="admin-sub">CONTROL PANEL SIGN-IN</p>

        <form id="adminLoginForm">
            <div class="mb-4">
                <label class="form-label" for="username"><i class="bi bi-person me-2"></i>Username or Email</label>
                <input type="text" class="form-control" id="username" placeholder="admin@gmail.com" required>
            </div>

            <div class="mb-5">
                <label class="form-label" for="password"><i class="bi bi-lock me-2"></i>Password</label>
                <input type="password" class="form-control" id="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-admin-login">
                <span>Access Console</span>
            </button>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Authorizing...';
            btn.disabled = true;

            fetch('../process/process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'admin_login',
                        username: username,
                        password: password
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'admin_dashboard.php';
                    } else {
                        alert(data.message);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred connecting to the security service.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    </script>
</body>

</html>