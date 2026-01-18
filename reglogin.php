<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Sign Up</title>
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
            </div>

            <div class="flex mb-6 bg-gray-100 p-1 rounded-full">
                <button onclick="switchForm('signin')" id="signinTab" class="flex-1 py-2 px-4 rounded-full text-sm font-medium transition-all duration-200 cursor-pointer whitespace-nowrap">Sign In</button>
                <button onclick="switchForm('signup')" id="signupTab" class="flex-1 py-2 px-4 rounded-full text-sm font-medium transition-all duration-200 cursor-pointer whitespace-nowrap">Sign Up</button>
            </div>

            <?php if (isset($_SESSION['register_success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="ri-check-line mr-2 text-lg"></i>
                    <span><?php echo $_SESSION['register_success']; unset($_SESSION['register_success']); ?></span>
                </div>
                <script>window.addEventListener('DOMContentLoaded',()=>switchForm('signup'));</script>
            <?php endif; ?>
            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="ri-error-warning-line mr-2 text-lg"></i>
                    <span><?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?></span>
                </div>
                <script>window.addEventListener('DOMContentLoaded',()=>switchForm('signup'));</script>
            <?php endif; ?>
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="ri-error-warning-line mr-2 text-lg"></i>
                    <span><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></span>
                </div>
            <?php endif; ?>
            <form id="signinForm" class="space-y-4" method="POST" action="login.php">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-mail-line text-gray-400"></i>
                    </div>
                    <input name="email" type="email" class="w-full pl-10 pr-3 py-2 border-none rounded bg-gray-50 text-sm" placeholder="Email address" required>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-lock-line text-gray-400"></i>
                    </div>
                    <input name="password" type="password" class="w-full pl-10 pr-10 py-2 border-none rounded bg-gray-50 text-sm" placeholder="Password" required>
                    <div class="password-toggle">
                        <i class="ri-eye-line text-gray-400"></i>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="text-sm text-primary hover:text-primary/80">Forgot password?</a>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-button hover:bg-primary/90 transition-colors duration-200 flex items-center justify-center">
                    <span class="submit-text">Sign In</span>
                    <i class="ri-loader-4-line animate-spin hidden ml-2"></i>
                </button>
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-200 rounded-button hover:bg-gray-50">
                        <i class="ri-google-fill text-xl"></i>
                    </button>
                    <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-200 rounded-button hover:bg-gray-50">
                        <i class="ri-apple-fill text-xl"></i>
                    </button>
                    <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-200 rounded-button hover:bg-gray-50">
                        <i class="ri-facebook-fill text-xl"></i>
                    </button>
                </div>
            </form>
            <form id="signupForm" class="space-y-4 hidden" method="POST" action="register.php">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-user-line text-gray-400"></i>
                    </div>
                    <input name="name" type="text" class="w-full pl-10 pr-3 py-2 border-none rounded bg-gray-50 text-sm" placeholder="Full name" required>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-mail-line text-gray-400"></i>
                    </div>
                    <input name="email" type="email" class="w-full pl-10 pr-3 py-2 border-none rounded bg-gray-50 text-sm" placeholder="Email address" required>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-lock-line text-gray-400"></i>
                    </div>
                    <input name="password" type="password" id="password" class="w-full pl-10 pr-10 py-2 border-none rounded bg-gray-50 text-sm" placeholder="Password" required>
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
                    <input name="confirm_password" type="password" class="w-full pl-10 pr-10 py-2 border-none rounded bg-gray-50 text-sm" placeholder="Confirm password" required>
                    <div class="password-toggle">
                        <i class="ri-eye-line text-gray-400"></i>
                    </div>
                </div>

                <div class="flex items-start">
                    <input type="checkbox" class="mt-1 rounded border-gray-300" required>
                    <label class="ml-2 text-sm text-gray-600">
                        I agree to the <a href="#" class="text-primary hover:text-primary/80">Terms of Service</a> and <a href="#" class="text-primary hover:text-primary/80">Privacy Policy</a>
                    </label>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-button hover:bg-primary/90 transition-colors duration-200 flex items-center justify-center">
                    <span class="submit-text">Create Account</span>
                    <i class="ri-loader-4-line animate-spin hidden ml-2"></i>
                </button>
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-200 rounded-button hover:bg-gray-50">
                        <i class="ri-google-fill text-xl"></i>
                    </button>
                    <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-200 rounded-button hover:bg-gray-50">
                        <i class="ri-apple-fill text-xl"></i>
                    </button>
                    <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-200 rounded-button hover:bg-gray-50">
                        <i class="ri-facebook-fill text-xl"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchForm(formType) {
            const signinForm = document.getElementById('signinForm');
            const signupForm = document.getElementById('signupForm');
            const signinTab = document.getElementById('signinTab');
            const signupTab = document.getElementById('signupTab');

            if (formType === 'signin') {
                signinForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
                signinTab.classList.add('bg-white', 'text-gray-900', 'shadow');
                signupTab.classList.remove('bg-white', 'text-gray-900', 'shadow');
            } else {
                signinForm.classList.add('hidden');
                signupForm.classList.remove('hidden');
                signinTab.classList.remove('bg-white', 'text-gray-900', 'shadow');
                signupTab.classList.add('bg-white', 'text-gray-900', 'shadow');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            switchForm('signin');

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

            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
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

            // Form validation for signup
            const signupForm = document.getElementById('signupForm');
            const confirmPasswordInput = signupForm.querySelector('input[name="confirm_password"]');
            
            signupForm.addEventListener('submit', (e) => {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const email = signupForm.querySelector('input[name="email"]').value;
                const name = signupForm.querySelector('input[name="name"]').value;
                
                // Clear previous error styles
                signupForm.querySelectorAll('input').forEach(input => {
                    input.classList.remove('border-red-500', 'bg-red-50');
                });
                
                let hasError = false;
                
                if (!name.trim()) {
                    signupForm.querySelector('input[name="name"]').classList.add('border-red-500', 'bg-red-50');
                    hasError = true;
                }
                
                if (!email.trim() || !email.includes('@')) {
                    signupForm.querySelector('input[name="email"]').classList.add('border-red-500', 'bg-red-50');
                    hasError = true;
                }
                
                if (password.length < 8) {
                    passwordInput.classList.add('border-red-500', 'bg-red-50');
                    hasError = true;
                }
                
                if (password !== confirmPassword) {
                    confirmPasswordInput.classList.add('border-red-500', 'bg-red-50');
                    hasError = true;
                }
                
                if (hasError) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                const submitBtn = signupForm.querySelector('button[type="submit"]');
                const submitText = submitBtn.querySelector('.submit-text');
                const loadingIcon = submitBtn.querySelector('i');
                
                submitText.textContent = 'Creating Account...';
                loadingIcon.classList.remove('hidden');
                submitBtn.disabled = true;
            });

            // Form validation for signin
            const signinForm = document.getElementById('signinForm');
            signinForm.addEventListener('submit', (e) => {
                const email = signinForm.querySelector('input[name="email"]').value;
                const password = signinForm.querySelector('input[name="password"]').value;
                
                // Clear previous error styles
                signinForm.querySelectorAll('input').forEach(input => {
                    input.classList.remove('border-red-500', 'bg-red-50');
                });
                
                let hasError = false;
                
                if (!email.trim() || !email.includes('@')) {
                    signinForm.querySelector('input[name="email"]').classList.add('border-red-500', 'bg-red-50');
                    hasError = true;
                }
                
                if (!password.trim()) {
                    signinForm.querySelector('input[name="password"]').classList.add('border-red-500', 'bg-red-50');
                    hasError = true;
                }
                
                if (hasError) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                const submitBtn = signinForm.querySelector('button[type="submit"]');
                const submitText = submitBtn.querySelector('.submit-text');
                const loadingIcon = submitBtn.querySelector('i');
                
                submitText.textContent = 'Signing In...';
                loadingIcon.classList.remove('hidden');
                submitBtn.disabled = true;
            });

            if (window.location.search.includes('register=1')) {
              window.addEventListener('DOMContentLoaded',()=>switchForm('signup'));
            }
        });
    </script>
</body>
</html>