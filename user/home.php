<?php session_start();
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
<title>SmartPark Innovations</title>
<script src="https://cdn.tailwindcss.com"></script>
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
<style>
:where([class^="ri-"])::before { content: "\f3c2"; }
body { font-family: 'Inter', sans-serif; }
.toast {
transform: translateX(150%);
transition: transform 0.3s ease-in-out;
}
.toast.show {
transform: translateX(0);
}
</style>
</head>
<body class="bg-gray-50">
<div id="cookieConsent" class="fixed bottom-0 left-0 right-0 bg-white shadow-lg p-4 z-50 hidden">
<div class="max-w-7xl mx-auto flex justify-between items-center">
<p class="text-gray-600">We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.</p>
<button onclick="acceptCookies()" class="bg-primary text-white px-6 py-2 !rounded-button cursor-pointer whitespace-nowrap">Accept</button>
</div>
</div>
<?php include 'includes/header.php'; ?>
<main>
<section class="relative overflow-hidden bg-gradient-to-r from-gray-50 to-gray-100 py-20">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
<div class="space-y-8">
<h1 class="text-5xl font-bold text-gray-900 leading-tight">Smart Parking Solutions for Modern Cities</h1>
<p class="text-xl text-gray-600">Find and reserve parking spots in real-time. Save time and reduce stress with our intelligent parking management system.</p>
<div class="flex space-x-4">
<a href="parking.php
"><button onclick="showParkingMap()" class="bg-white text-primary px-8 py-3 !rounded-button text-lg font-medium border-2 border-primary cursor-pointer whitespace-nowrap">Find Parking</button></a></div>
</div>
<div class="relative h-[400px]">
<img src="https://public.readdy.ai/ai/img_res/bf5db275a2369d36c21dee5579d72765.jpg" alt="Modern Parking Facility" class="rounded-lg shadow-xl object-cover w-full h-full">
</div>
</div>
</div>
</section>
<section class="py-20 bg-white">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<h2 class="text-3xl font-bold text-center mb-12">Our Key Features</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
<i class="ri-user-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-4">User Registration & Profile</h3>
<p class="text-gray-600 mb-6">Create your personal profile to manage vehicles, view parking history, and save favorite locations.</p>
<a href="#" class="text-primary font-medium hover:underline">Learn More</a>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
<i class="ri-parking-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-4">Real-time Availability</h3>
<p class="text-gray-600 mb-6">Check parking spot availability in real-time and reserve your space before arrival.</p>
<a href="#" class="text-primary font-medium hover:underline">Learn More</a>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
<i class="ri-secure-payment-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-4">Secure Payments</h3>
<p class="text-gray-600 mb-6">Multiple payment options with bank-grade security for worry-free transactions.</p>
<a href="#" class="text-primary font-medium hover:underline">Learn More</a>
</div>
</div>
</div>
</section>
<section class="py-20 bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<h2 class="text-3xl font-bold text-center mb-12">Live Parking Updates</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="col-span-2">
<div class="bg-white rounded-lg shadow-lg p-6 h-[500px] relative">
<img src="https://public.readdy.ai/gen_page/map_placeholder_1280x720.png" alt="Parking Map" class="absolute inset-0 w-full h-full object-cover rounded-lg">
<div class="absolute top-4 left-4 right-4">
<div class="bg-white rounded-lg shadow-sm p-2">
<div class="flex items-center">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-search-line text-gray-400"></i>
</div>
<input type="text" placeholder="Search location..." class="w-full border-none text-sm focus:ring-0">
</div>
</div>
</div>
</div>
</div>
<div class="space-y-6">
<div class="bg-white rounded-lg shadow-lg p-6">
<h3 class="text-xl font-semibold mb-4">Available Spots</h3>
<div class="space-y-4">
<div class="flex justify-between items-center">
<span class="text-gray-600">Central District</span>
<span class="text-primary font-medium">45 spots</span>
</div>
<div class="flex justify-between items-center">
<span class="text-gray-600">West Zone</span>
<span class="text-primary font-medium">32 spots</span>
</div>
<div class="flex justify-between items-center">
<span class="text-gray-600">East Side</span>
<span class="text-primary font-medium">28 spots</span>
</div>
</div>
</div>
<div class="bg-white rounded-lg shadow-lg p-6">
<h3 class="text-xl font-semibold mb-4">Parking Trends</h3>
<div id="parkingTrends" class="h-[200px]"></div>
</div>
</div>
</div>
</div>
</section>
<section class="py-20 bg-white">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<h2 class="text-3xl font-bold text-center mb-12">How to Reserve Your Spot</h2>
<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-20">
<div class="flex flex-col items-center text-center">
<div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
<i class="ri-map-pin-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-2">Find Location</h3>
<p class="text-gray-600">Search for parking spots near your destination</p>
</div>
<div class="flex flex-col items-center text-center">
<div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
<i class="ri-time-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-2">Select Time</h3>
<p class="text-gray-600">Choose your arrival and departure times</p>
</div>
<div class="flex flex-col items-center text-center">
<div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
<i class="ri-secure-payment-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-2">Make Payment</h3>
<p class="text-gray-600">Pay securely using your preferred method</p>
</div>
<div class="flex flex-col items-center text-center">
<div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
<i class="ri-parking-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-2">Park & Go</h3>
<p class="text-gray-600">Follow directions to your reserved spot</p>
</div>
</div>
<h2 class="text-3xl font-bold text-center mb-12">Secure Payment Options</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
<div>
<div class="bg-white rounded-lg shadow-lg p-8">
<div class="grid grid-cols-2 gap-6">
<div class="flex items-center space-x-4 p-4 border rounded-lg cursor-pointer">
<i class="ri-visa-fill text-2xl text-blue-600"></i>
<span class="font-medium">Credit Card</span>
</div>
<div class="flex items-center space-x-4 p-4 border rounded-lg cursor-pointer">
<i class="ri-alipay-fill text-2xl text-blue-500"></i>
<span class="font-medium">Alipay</span>
</div>
<div class="flex items-center space-x-4 p-4 border rounded-lg cursor-pointer">
<i class="ri-wechat-pay-fill text-2xl text-green-500"></i>
<span class="font-medium">WeChat Pay</span>
</div>
<div class="flex items-center space-x-4 p-4 border rounded-lg cursor-pointer">
<i class="ri-apple-fill text-2xl"></i>
<span class="font-medium">Apple Pay</span>
</div>
</div>
<button onclick="setupPayment()" class="w-full bg-primary text-white mt-8 py-3 !rounded-button font-medium cursor-pointer whitespace-nowrap">Set Up Payment</button>
</div>
</div>
<div class="space-y-6">
<h3 class="text-2xl font-semibold">Bank-Grade Security</h3>
<p class="text-gray-600">Your payments are protected with the latest encryption technology. We partner with trusted payment providers to ensure your transactions are always secure.</p>
<div class="flex space-x-4">
<img src="https://public.readdy.ai/ai/img_res/47b66f8cbe9fa8da9f8a9b012998002f.jpg" alt="Security Badge" class="w-16 h-16">
<img src="https://public.readdy.ai/ai/img_res/6a15166b08a9af5e9e63ac83436a76e7.jpg" alt="SSL Badge" class="w-16 h-16">
</div>
</div>
</div>
</div>
</section>
</main>
<?php include 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
showCookieConsent();
initParkingTrends();
});
function showCookieConsent() {
document.getElementById('cookieConsent').classList.remove('hidden');
}
function acceptCookies() {
document.getElementById('cookieConsent').classList.add('hidden');
showToast('Cookies accepted');
}
function showLoginModal() {
document.getElementById('loginModal').classList.remove('hidden');
}
function closeLoginModal() {
document.getElementById('loginModal').classList.add('hidden');
}
function showRegisterModal() {
document.getElementById('registerModal').classList.remove('hidden');
}
function closeRegisterModal() {
document.getElementById('registerModal').classList.add('hidden');
}
function handleLogin(event) {
event.preventDefault();
const form = event.target;
const email = form.querySelector('input[type="email"]').value;
const password = form.querySelector('input[type="password"]').value;
// Remove existing error messages
const existingError = form.querySelector('.error-message');
if (existingError) {
existingError.remove();
}
// Mock credentials validation
if (email === 'demo@example.com' && password === 'password123') {
closeLoginModal();
showToast('Login successful');
// Redirect to dashboard
window.location.href = '/dashboard.html';
} else {
// Display error message
const errorDiv = document.createElement('div');
errorDiv.className = 'error-message text-red-500 text-sm mt-2';
errorDiv.textContent = 'Invalid email or password';
form.querySelector('button[type="submit"]').insertAdjacentElement('beforebegin', errorDiv);
}
}
function handleRegister(event) {
event.preventDefault();
closeRegisterModal();
showToast('Registration successful');
}
function showToast(message) {
const toast = document.getElementById('toast');
toast.textContent = message;
toast.classList.add('show');
setTimeout(() => {
toast.classList.remove('show');
}, 3000);
}
function setupPayment() {
showToast('Payment setup initiated');
}
function subscribeNewsletter(event) {
event.preventDefault();
showToast('Successfully subscribed to newsletter');
}
function initParkingTrends() {
const chart = echarts.init(document.getElementById('parkingTrends'));
const option = {
animation: false,
grid: {
top: 10,
right: 10,
bottom: 20,
left: 30
},
xAxis: {
type: 'category',
data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
axisLine: {
lineStyle: {
color: '#E5E7EB'
}
},
axisLabel: {
color: '#1F2937'
}
},
yAxis: {
type: 'value',
axisLine: {
show: false
},
axisLabel: {
color: '#1F2937'
},
splitLine: {
lineStyle: {
color: '#E5E7EB'
}
}
},
series: [{
data: [150, 230, 224, 218, 135, 147, 260],
type: 'line',
smooth: true,
symbol: 'none',
itemStyle: {
color: 'rgba(87, 181, 231, 1)'
},
areaStyle: {
color: {
type: 'linear',
x: 0,
y: 0,
x2: 0,
y2: 1,
colorStops: [{
offset: 0,
color: 'rgba(87, 181, 231, 0.2)'
}, {
offset: 1,
color: 'rgba(87, 181, 231, 0)'
}]
}
}
}],
tooltip: {
trigger: 'axis',
backgroundColor: 'rgba(255, 255, 255, 0.9)',
borderColor: '#E5E7EB',
textStyle: {
color: '#1F2937'
}
}
};
chart.setOption(option);
}
</script>
</body>
</html>
