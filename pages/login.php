<?php
/**
 * Login Page
 */

require_once dirname(__DIR__) . '/config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(APP_URL . '/pages/dashboard.php');
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . APP_NAME; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 15px;
            font-size: 14px;
        }

        .spinner-border-sm {
            width: 16px;
            height: 16px;
            border-width: 2px;
        }

        .login-footer {
            text-align: center;
            padding: 20px 30px 30px;
            color: #64748b;
            font-size: 13px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            user-select: none;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .position-relative {
            position: relative;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1><?php echo APP_NAME; ?></h1>
        <p>School Money Collection System</p>
    </div>

    <div class="login-body">
        <div id="alert-container"></div>

        <form id="loginForm">
            <div class="mb-3">
                <label for="contact" class="form-label">Contact Number</label>
                <input type="text" class="form-control" id="contact" name="contact"
                       placeholder="0XXXXXXXXX" maxlength="10" required>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Enter your password" required>
                    <span class="password-toggle" onclick="togglePassword()">
                        <span id="toggleIcon">👁️</span>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-login" id="loginBtn">
                <span id="btnText">Sign In</span>
                <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>
    </div>

    <div class="login-footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        <p>Version <?php echo APP_VERSION; ?></p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const alertContainer = document.getElementById('alert-container');

    // Handle form submission
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get form data
        const contact = document.getElementById('contact').value.trim();
        const password = document.getElementById('password').value;

        // Validate
        if (!contact || !password) {
            showAlert('Please fill in all fields', 'danger');
            return;
        }

        // Disable button and show spinner
        loginBtn.disabled = true;
        btnText.textContent = 'Signing in...';
        btnSpinner.classList.remove('d-none');

        try {
            // Send login request
            const response = await fetch('<?php echo APP_URL; ?>/api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ contact, password })
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');

                // Redirect to dashboard after 1 second
                setTimeout(() => {
                    window.location.href = '<?php echo APP_URL; ?>/pages/dashboard.php';
                }, 1000);
            } else {
                showAlert(result.message, 'danger');
                loginBtn.disabled = false;
                btnText.textContent = 'Sign In';
                btnSpinner.classList.add('d-none');
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            loginBtn.disabled = false;
            btnText.textContent = 'Sign In';
            btnSpinner.classList.add('d-none');
        }
    });

    // Show alert message
    function showAlert(message, type) {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.textContent = '🙈';
        } else {
            passwordInput.type = 'password';
            toggleIcon.textContent = '👁️';
        }
    }

    // Format contact number (allow only digits)
    document.getElementById('contact').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>

</body>
</html>
