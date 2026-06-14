<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Nova Street Fashion</title>
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
        <div class="auth-image-side" style="background-image: url('assets/img/men.png');">
            <div class="auth-image-overlay">
                <a href="index.php" class="text-white text-decoration-none fs-3 fw-light mb-auto">Nova Street.</a>
                <div class="mt-auto ps-4">
                    <h2 class="shop-title">Join</h2>
                    <ul class="auth-nav">
                        <li class="active"><a href="#">Membership</a></li>
                        <li><a href="#">Privileges</a></li>
                        <li><a href="#">Style Profile</a></li>
                        <li><a href="#">Orders</a></li>
                        <li><a href="#">Rewards</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side py-5">
            <a href="index.php" class="auth-close-btn border border-white rounded-pill p-3 lh-1"><i
                    class="bi bi-x-lg fs-6"></i></a>

            <div class="mx-auto" style="max-width: 500px; width: 100%;">
                <h2 class="auth-title fw-bold">CREATE ACCOUNT</h2>
                <p class="auth-subtitle">Become a member today!</p>

                <form class="auth-form mt-4">
                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="signupFirstName"><i class="bi bi-person me-2"></i>First Name</label>
                            <input type="text" class="form-control" id="signupFirstName" placeholder="John" required>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="signupLastName">Last Name</label>
                            <input type="text" class="form-control" id="signupLastName" placeholder="Doe" required>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="signupContact"><i class="bi bi-telephone me-2"></i>Contact Number</label>
                        <input type="tel" class="form-control" id="signupContact" placeholder="+94 77 123 4567" required>
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="signupEmail"><i class="bi bi-envelope me-2"></i>Email Address</label>
                        <input type="email" class="form-control" id="signupEmail" placeholder="john@example.com" required>
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="signupGender"><i class="bi bi-gender-ambiguous me-2"></i>Gender</label>
                        <select class="form-select" id="signupGender" required>
                            <option selected disabled value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-1 position-relative">
                            <label class="form-label" for="signupPass"><i class="bi bi-lock me-2"></i>Password</label>
                            <input type="password" class="form-control" id="signupPass" placeholder="••••••••" required>
                            <i class="bi bi-eye password-toggle" onclick="togglePassword('signupPass', this)"></i>
                        </div>
                        <div class="col-md-6 mb-1 position-relative">
                            <label class="form-label" for="signupRePass">Re-Password</label>
                            <input type="password" class="form-control" id="signupRePass" placeholder="••••••••"
                                required>
                            <i class="bi bi-eye password-toggle" onclick="togglePassword('signupRePass', this)"></i>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-auth">
                            <span class="ps-2">Sign Up</span>
                            <i class="bi bi-person-plus pe-2"></i>
                        </button>
                    </div>
                </form>

                <p class="text-center mt-4 text-white-50 small">
                    Already have an account? <a href="login.php" class="text-white fw-bold text-decoration-none">Login Now</a>
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
            
            const firstName = document.getElementById('signupFirstName').value;
            const lastName = document.getElementById('signupLastName').value;
            const contact = document.getElementById('signupContact').value;
            const email = document.getElementById('signupEmail').value;
            const gender = document.getElementById('signupGender').value;
            const password = document.getElementById('signupPass').value;
            const rePassword = document.getElementById('signupRePass').value;

            if (password !== rePassword) {
                alert("Passwords do not match!");
                return;
            }

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
                    action: 'signup',
                    first_name: firstName,
                    last_name: lastName,
                    contact: contact,
                    email: email,
                    gender: gender,
                    password: password
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'login.php';
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
