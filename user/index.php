<?php
// Set session cookie lifetime to 2 hours (7200 seconds)
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);
session_set_cookie_params(7200);
// session_start();
// Remove the redirect so all users can access the page
// if (!isset($_SESSION['user'])) {
//   header('Location: ../reglogin.php');
//   exit();
// }
// Prevent page caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require_once '../components/function.php';
$spots = get_parking_spots();
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
  <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center px-4 sm:px-6 lg:px-8">
<p class="text-gray-600">We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.</p>
<button onclick="acceptCookies()" class="bg-primary text-white px-6 py-2 !rounded-button cursor-pointer whitespace-nowrap">Accept</button>
</div>
</div>
<!-- Login/Register Modal -->
<div id="authModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-[9999] hidden">
  <div class="bg-white rounded-xl shadow-lg p-8 max-w-sm w-full relative z-[10000]">
    <button onclick="closeAuthModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 z-[10001]"><i class="ri-close-line text-2xl"></i></button>
    <h2 class="text-2xl font-bold mb-4 text-primary text-center">Login or Register</h2>
    <p class="text-gray-600 mb-6 text-center">You need to be logged in to access this feature.</p>
    <div class="flex space-x-4 justify-center">
      <a href="../reglogin.php"><button class="bg-primary text-white px-6 py-2 rounded-button font-medium">Login</button></a>
      <a href="../reglogin.php?register=1"><button class="bg-white border border-primary text-primary px-6 py-2 rounded-button font-medium">Register</button></a>
    </div>
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
<?php if (!isset($_SESSION['user'])): ?>
<a href="#" onclick="showAuthModal(); return false;"><button class="bg-primary text-white px-8 py-3 !rounded-button text-lg font-medium cursor-pointer whitespace-nowrap">Register Now</button></a>
<?php endif; ?>
<?php if (!isset($_SESSION['user'])): ?>
<button onclick="showAuthModal()" class="bg-white text-primary px-8 py-3 !rounded-button text-lg font-medium border-2 border-primary cursor-pointer whitespace-nowrap">Find Parking</button>
<?php else: ?>
<a href="parking.php"><button class="bg-white text-primary px-8 py-3 !rounded-button text-lg font-medium border-2 border-primary cursor-pointer whitespace-nowrap">Find Parking</button></a>
<?php endif; ?>
</div>
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
<?php if (!isset($_SESSION['user'])): ?>
<a href="#" onclick="showAuthModal(); return false;" class="text-primary font-medium hover:underline">Learn More</a>
<?php else: ?>
<a href="service.php" class="text-primary font-medium hover:underline">Learn More</a>
<?php endif; ?>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
<i class="ri-parking-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-4">Real-time Availability</h3>
<p class="text-gray-600 mb-6">Check parking spot availability in real-time and reserve your space before arrival.</p>
<?php if (!isset($_SESSION['user'])): ?>
<a href="#" onclick="showAuthModal(); return false;" class="text-primary font-medium hover:underline">Learn More</a>
<?php else: ?>
<a href="service.php" class="text-primary font-medium hover:underline">Learn More</a>
<?php endif; ?>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
<i class="ri-secure-payment-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-4">Secure Payments</h3>
<p class="text-gray-600 mb-6">Multiple payment options with bank-grade security for worry-free transactions.</p>
<?php if (!isset($_SESSION['user'])): ?>
<a href="#" onclick="showAuthModal(); return false;" class="text-primary font-medium hover:underline">Learn More</a>
<?php else: ?>
<a href="service.php" class="text-primary font-medium hover:underline">Learn More</a>
<?php endif; ?>
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
  <div id="map" style="width: 100%; height: 100%; border-radius: 0.75rem;"></div>
</div>
</div>
<div class="space-y-6">
<div class="bg-white rounded-lg shadow-lg p-6 h-[500px] overflow-y-auto">
<h3 class="text-xl font-semibold mb-4">Available Spots</h3>
<?php
// Show only 3 random available spots
$available_spots = array_filter($spots, function($s) { return $s['available_spots'] > 0; });
if (count($available_spots) > 3) {
    $keys = array_rand($available_spots, 3);
    // array_rand returns int if 1, array if >1
    $random_spots = is_array($keys) ? array_intersect_key($available_spots, array_flip($keys)) : [$available_spots[$keys]];
} else {
    $random_spots = $available_spots;
}
?>
<?php foreach ($random_spots as $spot): ?>
  <div class="mb-4 p-4 border-b last:border-b-0">
    <div class="flex justify-between items-center mb-1">
      <span class="font-semibold text-primary"><?php echo htmlspecialchars($spot['name']); ?></span>
      <span class="text-green-600 font-medium"><?php echo (int)$spot['available_spots']; ?> spots</span>
    </div>
    <div class="text-gray-500 text-sm mb-1"><?php echo htmlspecialchars($spot['location']); ?></div>
    <div class="text-xs text-gray-400">Open: <?php echo htmlspecialchars($spot['operating_hours']); ?></div>
  </div>
<?php endforeach; ?>
<?php if (!isset($_SESSION['user'])): ?>
<a href="#" onclick="showAuthModal(); return false;" class="block mt-6 text-center bg-primary text-white py-2 rounded-lg font-semibold hover:bg-primary/90 transition">More Parking Options</a>
<?php else: ?>
<a href="parking.php" class="block mt-6 text-center bg-primary text-white py-2 rounded-lg font-semibold hover:bg-primary/90 transition">More Parking Options</a>
<?php endif; ?>
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
<img src="../assets/images/mpesa.png" alt="M-Pesa" class="h-8 w-auto">
<span class="font-medium">M-Pesa</span>
</div>
<div class="flex items-center space-x-4 p-4 border rounded-lg cursor-pointer">
<img src="../assets/images/Equity.jpg" alt="Equity" class="h-8 w-auto">
<span class="font-medium">Equity</span>
</div>
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
function showAuthModal() {
  document.getElementById('authModal').classList.remove('hidden');
}
function closeAuthModal() {
  document.getElementById('authModal').classList.add('hidden');
}
<?php if (!isset($_SESSION['user'])): ?>
document.addEventListener('DOMContentLoaded', function() {
  // Register Now button
  document.querySelectorAll('button[onclick="showRegisterModal()"], a[href="parking.php"], a.text-primary.font-medium.hover\:underline').forEach(function(el) {
    el.addEventListener('click', function(e) {
      e.preventDefault();
      showAuthModal();
    });
  });
});
<?php endif; ?>
</script>
<?php
$spots_for_map = array_map(function($s) {
    return [
        'id' => $s['id'],
        'name' => $s['name'],
        'lat' => (float)$s['latitude'],
        'lng' => (float)$s['longitude'],
        'location' => $s['location'],
        'available_spots' => $s['available_spots'],
    ];
}, $spots);
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var mapEl = document.getElementById('map');
  if (mapEl) {
    var map = L.map('map', { zoomControl: true }).setView([-1.286389, 36.817223], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    map.zoomControl.setPosition('topright');
    var spots = <?php echo json_encode(array_values($spots_for_map)); ?>;
    spots.forEach(function(spot) {
      if (!spot.lat || !spot.lng) return;
      var marker = L.marker([spot.lat, spot.lng]).addTo(map);
      var popupContent = '<div><strong>' + spot.name + '</strong><br>' + spot.location + '<br>Available: ' + spot.available_spots + '</div>';
      marker.bindPopup(popupContent);
    });
  }
});
</script>
<script>
// Keep session alive for logged-in users
<?php if (isset($_SESSION['user'])): ?>
setInterval(function() {
  fetch('keepalive.php', { credentials: 'include' });
}, 5 * 60 * 1000); // every 5 minutes
<?php endif; ?>
</script>
</body>
</html>
