<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SmartPark</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#20215B',
                        secondary: '#6B7280'
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .form-container {
            min-height: 100vh;
            background-image: url('https://readdy.ai/api/search-image?query=abstract modern geometric pattern with soft gradient colors, minimalist design, light and airy feeling, professional and clean look, subtle texture&width=1920&height=1080&seq=auth&flag=d98283c86e438c690cae22067cfde1c7_bg&orientation=landscape');
            background-size: cover;
            background-position: center;
        }
        .auth-form {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.9);
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .strength-meter {
            height: 4px;
            transition: all 0.3s;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="form-container flex items-center justify-center p-4">
        <div class="auth-form w-full max-w-md p-8 rounded-lg shadow-xl">
            <div class="text-center mb-8">
                <h1 class="font-['Pacifico'] text-3xl text-primary">SmartPark</h1>
                <p class="text-gray-600 mt-2">Set your new password</p>
            </div>

            <?php
            require_once 'components/function.php';
            
            $token = $_GET['token'] ?? '';
            $error_message = '';
            $valid_token = false;
            
            if ($token) {
                $conn = db_connect();
                $stmt = $conn->prepare('SELECT email, expires_at, used FROM password_resets WHERE token = ?');
                $stmt->bind_param('s', $token);
                $stmt->execute();
                $result = $stmt->get_result();
                $reset_data = $result->fetch_assoc();
                $stmt->close();
                $conn->close();
                
                if ($reset_data) {
                    if ($reset_data['used'] == 1) {
                        $error_message = 'This reset link has already been used. Please request a new password reset.';
                    } elseif (strtotime($reset_data['expires_at']) < gmdate('U')) {
                        $error_message = 'This reset link has expired. Please request a new password reset.';
                    } else {
                        $valid_token = true;
                    }
                } else {
                    $error_message = 'Invalid reset link. Please check your email and try again.';
                }
            } else {
                $error_message = 'No reset token provided.';
            }
            ?>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="ri-error-warning-line mr-2 text-lg"></i>
                    <span><?php echo $error_message; ?></span>
                </div>
                <div class="text-center">
                    <a href="forgot_password.php" class="text-sm text-primary hover:text-primary/80 font-medium">
                        Request New Reset Link
                    </a>
                </div>
            <?php elseif ($valid_token): ?>
                <?php if (isset($_SESSION['reset_password_success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                        <i class="ri-check-line mr-2 text-lg"></i>
                        <span><?php echo $_SESSION['reset_password_success']; unset($_SESSION['reset_password_success']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['reset_password_error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                        <i class="ri-error-warning-line mr-2 text-lg"></i>
                        <span><?php echo $_SESSION['reset_password_error']; unset($_SESSION['reset_password_error']); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="process_reset_password.php" class="space-y-6">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ri-lock-password-line text-primary text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Create New Password</h2>
                        <p class="text-gray-600 text-sm">Enter your new password below.</p>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-lock-line text-gray-400"></i>
                        </div>
                        <input name="password" type="password" id="password" class="w-full pl-10 pr-10 py-3 border-none rounded bg-gray-50 text-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="New password" required>
                        <div class="password-toggle">
                            <i class="ri-eye-line text-gray-400"></i>
                        </div>
                    </div>

                    <div class="strength-meter-container">
                        <div class="strength-meter bg-gray-200 rounded-full">
                            <div id="strengthBar" class="h-full rounded-full" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Password strength: <span id="strengthText">Weak</span></p>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-lock-line text-gray-400"></i>
                        </div>
                        <input name="confirm_password" type="password" class="w-full pl-10 pr-10 py-3 border-none rounded bg-gray-50 text-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Confirm new password" required>
                        <div class="password-toggle">
                            <i class="ri-eye-line text-gray-400"></i>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-primary text-white py-3 px-4 rounded-button hover:bg-primary/90 transition-colors duration-200 flex items-center justify-center">
                        <span class="submit-text">Reset Password</span>
                        <i class="ri-loader-4-line animate-spin hidden ml-2"></i>
                    </button>

                    <div class="text-center">
                        <a href="reglogin.php" class="text-sm text-primary hover:text-primary/80 font-medium">
                            <i class="ri-arrow-left-line mr-1"></i>
                            Back to Sign In
                        </a>
                    </div>
                </form>
            <?php endif; ?>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500">
                    Remember your password? 
                    <a href="reglogin.php" class="text-primary hover:text-primary/80 font-medium">Sign in</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Auto-hide notifications after 5 seconds
            const notifications = document.querySelectorAll('.bg-green-100, .bg-red-100');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.transition = 'opacity 0.5s ease-out';
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.remove();
                    }, 500);
                }, 5000);
            });

            // Password toggle functionality
            const passwordToggles = document.querySelectorAll('.password-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    const input = e.target.closest('.relative').querySelector('input');
                    const icon = e.target.closest('.password-toggle').querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
                    }
                });
            });

            // Password strength meter
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if (passwordInput && strengthBar && strengthText) {
                passwordInput.addEventListener('input', (e) => {
                    const password = e.target.value;
                    let strength = 0;
                    
                    if (password.length >= 8) strength += 25;
                    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
                    if (password.match(/\d/)) strength += 25;
                    if (password.match(/[^a-zA-Z\d]/)) strength += 25;

                    strengthBar.style.width = strength + '%';
                    
                    if (strength <= 25) {
                        strengthBar.classList.remove('bg-yellow-500', 'bg-green-500');
                        strengthBar.classList.add('bg-red-500');
                        strengthText.textContent = 'Weak';
                    } else if (strength <= 50) {
                        strengthBar.classList.remove('bg-red-500', 'bg-green-500');
                        strengthBar.classList.add('bg-yellow-500');
                        strengthText.textContent = 'Medium';
                    } else {
                        strengthBar.classList.remove('bg-yellow-500', 'bg-red-500');
                        strengthBar.classList.add('bg-green-500');
                        strengthText.textContent = 'Strong';
                    }
                });
            }

            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    const password = form.querySelector('input[name="password"]').value;
                    const confirmPassword = form.querySelector('input[name="confirm_password"]').value;
                    
                    // Clear previous error styles
                    form.querySelectorAll('input').forEach(input => {
                        input.classList.remove('border-red-500', 'bg-red-50');
                    });
                    
                    let hasError = false;
                    
                    if (password.length < 8) {
                        form.querySelector('input[name="password"]').classList.add('border-red-500', 'bg-red-50');
                        hasError = true;
                    }
                    
                    if (password !== confirmPassword) {
                        form.querySelector('input[name="confirm_password"]').classList.add('border-red-500', 'bg-red-50');
                        hasError = true;
                    }
                    
                    if (hasError) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const submitText = submitBtn.querySelector('.submit-text');
                    const loadingIcon = submitBtn.querySelector('i');
                    
                    submitText.textContent = 'Resetting...';
                    loadingIcon.classList.remove('hidden');
                    submitBtn.disabled = true;
                });
            }
        });
    </script>
</body>
</html> 