<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SmartPark</title>
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
    </style>
</head>
<body class="bg-gray-50">
    <div class="form-container flex items-center justify-center p-4">
        <div class="auth-form w-full max-w-md p-8 rounded-lg shadow-xl">
            <div class="text-center mb-8">
                <h1 class="font-['Pacifico'] text-3xl text-primary">SmartPark</h1>
                <p class="text-gray-600 mt-2">Reset your password</p>
            </div>

            <?php if (isset($_SESSION['forgot_password_success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="ri-check-line mr-2 text-lg"></i>
                    <span><?php echo $_SESSION['forgot_password_success']; unset($_SESSION['forgot_password_success']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['forgot_password_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="ri-error-warning-line mr-2 text-lg"></i>
                    <span><?php echo $_SESSION['forgot_password_error']; unset($_SESSION['forgot_password_error']); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="process_forgot_password.php" class="space-y-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-lock-password-line text-primary text-2xl"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Forgot your password?</h2>
                    <p class="text-gray-600 text-sm">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-mail-line text-gray-400"></i>
                    </div>
                    <input name="email" type="email" class="w-full pl-10 pr-3 py-3 border-none rounded bg-gray-50 text-sm focus:ring-2 focus:ring-primary focus:outline-none" placeholder="Enter your email address" required>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-3 px-4 rounded-button hover:bg-primary/90 transition-colors duration-200 flex items-center justify-center">
                    <span class="submit-text">Send Reset Link</span>
                    <i class="ri-loader-4-line animate-spin hidden ml-2"></i>
                </button>

                <div class="text-center">
                    <a href="reglogin.php" class="text-sm text-primary hover:text-primary/80 font-medium">
                        <i class="ri-arrow-left-line mr-1"></i>
                        Back to Sign In
                    </a>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500">
                    Don't have an account? 
                    <a href="reglogin.php?register=1" class="text-primary hover:text-primary/80 font-medium">Sign up</a>
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

            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', (e) => {
                const email = form.querySelector('input[name="email"]').value;
                
                // Clear previous error styles
                form.querySelectorAll('input').forEach(input => {
                    input.classList.remove('border-red-500', 'bg-red-50');
                });
                
                if (!email.trim() || !email.includes('@')) {
                    form.querySelector('input[name="email"]').classList.add('border-red-500', 'bg-red-50');
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const submitText = submitBtn.querySelector('.submit-text');
                const loadingIcon = submitBtn.querySelector('i');
                
                submitText.textContent = 'Sending...';
                loadingIcon.classList.remove('hidden');
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html> 