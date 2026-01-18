<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ../reglogin.php');
  exit();
}
// Prevent page caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Our Services - SmartPark Innovations</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    .fade-in {
      opacity: 0;
      transform: translateY(30px) scale(0.98);
      animation: fadeInUp 0.7s cubic-bezier(.4,2,.6,1) forwards;
    }
    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: none;
      }
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include 'includes/header.php'; ?>
<main class="max-w-6xl mx-auto px-4 py-12">
  <h1 class="text-4xl font-bold text-center mb-4 font-['Pacifico'] text-primary">Our Services</h1>
  <p class="text-center text-gray-500 mb-12 text-lg">SmartPark Innovations offers a suite of modern parking solutions designed for convenience, security, and efficiency.</p>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
    <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center fade-in" style="animation-delay:0.1s">
      <i class="ri-map-pin-2-fill text-4xl text-primary mb-4 animate-bounce"></i>
      <h2 class="text-xl font-semibold mb-2">Real-time Parking Availability</h2>
      <p class="text-gray-500 text-center">Find available parking spots instantly with live updates and interactive maps.</p>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center fade-in" style="animation-delay:0.2s">
      <i class="ri-shield-check-fill text-4xl text-primary mb-4 animate-pulse"></i>
      <h2 class="text-xl font-semibold mb-2">Secure Payments</h2>
      <p class="text-gray-500 text-center">Pay securely using M-Pesa, Equity, and more. Your transactions are encrypted and safe.</p>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center fade-in" style="animation-delay:0.3s">
      <i class="ri-calendar-check-fill text-4xl text-primary mb-4 animate-bounce"></i>
      <h2 class="text-xl font-semibold mb-2">Reservation Management</h2>
      <p class="text-gray-500 text-center">Book, manage, and view your parking reservations with ease from any device.</p>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center fade-in" style="animation-delay:0.4s">
      <i class="ri-customer-service-2-fill text-4xl text-primary mb-4 animate-pulse"></i>
      <h2 class="text-xl font-semibold mb-2">24/7 Customer Support</h2>
      <p class="text-gray-500 text-center">Get help anytime with our dedicated support team, ready to assist you 24/7.</p>
    </div>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#20215B',
        secondary: '#64748B'
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
<script>
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.fade-in').forEach((el, i) => {
    el.style.animationDelay = (i * 0.1 + 0.1) + 's';
  });
});
</script>
</body>
</html>
