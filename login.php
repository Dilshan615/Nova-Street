<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="auth-wrapper">
        <!-- Image Side -->
        <div class="auth-image-side" style="background-image: url('assets/img/blog.png');">
            <div class="auth-image-overlay">
                <a href="index.php" class="text-white text-decoration-none fs-3 fw-light mb-auto">Nova Street.</a>
                <div class="mt-auto ps-4">
                    <h2 class="shop-title">Shop</h2>
                    <ul class="auth-nav">
                        <li class="active"><a href="#">New Arrivals</a></li>
                        <li><a href="#">Winters</a></li>
                        <li><a href="#">Women's</a></li>
                        <li><a href="#">Men's</a></li>
                        <li><a href="#">Kids</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <a href="index.php" class="auth-close-btn border border-white rounded-pill p-3 lh-1"><i
                    class="bi bi-x-lg fs-6"></i></a>

            <div class="mx-auto" style="max-width: 400px; width: 100%;">
                <h2 class="auth-title fw-bold">EXISTING MEMBER</h2>
                <p class="auth-subtitle">Welcome Back!</p>

                <form class="auth-form mt-5">
                    <div class="mb-4">
                        <label class="form-label" for="loginEmail"><i class="bi bi-envelope me-2"></i>Email Address</label>
                        <input type="email" class="form-control" id="loginEmail" placeholder="Jamesthomas@mail.com" required>
                    </div>

                    <div class="mb-4 position-relative">
                        <label class="form-label" for="loginPassword"><i class="bi bi-lock me-2"></i>Password</label>
                        <input type="password" class="form-control" id="loginPassword" placeholder="Enter Password"
                            required>
                        <i class="bi bi-eye password-toggle" onclick="togglePassword('loginPassword', this)"></i>
                    </div>

                    <a href="#" class="forgot-password">Forgot Password?</a>

                    <div class="text-center">
                        <button type="submit" class="btn btn-auth">
                            <span class="ps-2">Continue</span>
                            <i class="bi bi-arrow-right pe-2"></i>
                        </button>
                    </div>
                </form>

                <div class="text-center my-4 position-relative">
                    <hr class="opacity-10">
                    <span
                        class="position-absolute top-50 start-50 translate-middle bg-dark px-3 text-white-50 small">OR</span>
                </div>

                <div class="social-auth">
                    <a href="#" class="social-btn"><i class="bi bi-google"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-apple"></i></a>
                </div>

                <p class="text-center mt-5 text-white-50">
                    Don't have an account? <a href="signup.php"
                        class="text-white fw-bold text-decoration-none">Register Now</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        document.querySelector('.auth-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
            btn.disabled = true;

            fetch('process/process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'login',
                    email: email,
                    password: password
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('novastreet-user', JSON.stringify(data.user));
                    alert(data.message);
                    const urlParams = new URLSearchParams(window.location.search);
                    const redirectTo = urlParams.get('redirect');
                    window.location.href = redirectTo ? redirectTo : 'index.php';
                } else {
                    alert(data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred connecting to the server.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    </script>
</body>

</html>
