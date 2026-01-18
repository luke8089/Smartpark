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
<title>SmartPark Innovations - Intelligent Parking Solutions</title>
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

<?php include 'includes/header.php'; ?>
<main>
    <div class="relative">
        <img src="https://public.readdy.ai/ai/img_res/e744136eada7ec7c4aaaf434f92fdf9a.jpg" class="w-full h-[400px] object-cover" alt="About Us Hero">
        <div class="absolute inset-0 bg-gradient-to-r from-gray-900/80 to-gray-900/40">
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center">
        <div class="text-white">
        <div class="flex items-center space-x-2 text-sm mb-4">
        <a href="https://readdy.ai/home/c7c6837e-6ffb-405e-b922-2239ef458e8c/189b98d7-309d-4459-b539-98440a778031" data-readdy="true" class="hover:text-primary">Home</a>
        <i class="ri-arrow-right-s-line"></i>
        <span>About Us</span>
        </div>
        <h1 class="text-5xl font-bold mb-4">Transforming Urban Parking</h1>
        <p class="text-xl max-w-2xl">Leading the revolution in smart parking solutions with innovative technology and sustainable practices.</p>
        </div>
        </div>
        </div>
        </div>

<section class="py-20 bg-white">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-20">
<div class="relative h-[400px]">
<img src="https://public.readdy.ai/ai/img_res/0fb32bce7a67f446c4f250889247b377.jpg" alt="Our Team" class="rounded-lg shadow-xl object-cover w-full h-full">
</div>
<div class="space-y-6">
<h2 class="text-3xl font-bold text-gray-900">Our Story</h2>
<p class="text-gray-600">Founded in 2020, SmartPark Innovations emerged from a simple observation: urban parking needed a smart revolution. Our team of technology enthusiasts and urban planning experts came together to create a solution that would transform the way people park in cities.</p>
<p class="text-gray-600">Today, we serve over 50 cities worldwide, helping millions of drivers find parking spaces efficiently while reducing urban congestion and emissions.</p>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
<div class="text-center">
<div class="text-4xl font-bold text-primary mb-2">500+</div>
<div class="text-gray-600">Parking Locations</div>
</div>
<div class="text-center">
<div class="text-4xl font-bold text-primary mb-2">2M+</div>
<div class="text-gray-600">Happy Users</div>
</div>
<div class="text-center">
<div class="text-4xl font-bold text-primary mb-2">50+</div>
<div class="text-gray-600">Partner Cities</div>
</div>
</div>
<div class="bg-gray-50 rounded-2xl p-12 mb-20">
<h2 class="text-3xl font-bold text-center mb-12">Our Mission & Values</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="bg-white p-6 rounded-lg shadow-lg">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4">
<i class="ri-focus-3-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Innovation First</h3>
<p class="text-gray-600">Continuously pushing boundaries to create smarter parking solutions.</p>
</div>
<div class="bg-white p-6 rounded-lg shadow-lg">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4">
<i class="ri-earth-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Sustainability</h3>
<p class="text-gray-600">Committed to reducing environmental impact through efficient parking.</p>
</div>
<div class="bg-white p-6 rounded-lg shadow-lg">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4">
<i class="ri-team-line text-primary text-2xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Community Focus</h3>
<p class="text-gray-600">Building solutions that benefit entire urban communities.</p>
</div>
</div>
</div>
<div class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-16">
    <h2 class="text-3xl font-bold text-gray-900">Our Technology</h2>
    <p class="text-gray-600 mt-4">Advanced solutions for modern parking challenges</p>
    </div>
    
    <div class="grid md:grid-cols-3 gap-8">
    <div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4">
    <i class="ri-sensor-line text-primary text-2xl"></i>
    </div>
    <h3 class="text-xl font-bold mb-2">IoT Integration</h3>
    <p class="text-gray-600">Real-time monitoring and data collection through advanced sensors</p>
    <div id="iotChart" class="h-48 mt-4"></div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4">
    <i class="ri-cloud-line text-primary text-2xl"></i>
    </div>
    <h3 class="text-xl font-bold mb-2">Cloud Infrastructure</h3>
    <p class="text-gray-600">Scalable cloud-based system for reliable performance</p>
    <div id="cloudChart" class="h-48 mt-4"></div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4">
    <i class="ri-brain-line text-primary text-2xl"></i>
    </div>
    <h3 class="text-xl font-bold mb-2">AI Analytics</h3>
    <p class="text-gray-600">Predictive analytics for optimal resource allocation</p>
    <div id="aiChart" class="h-48 mt-4"></div>
    </div>
    </div>
    </div>
    </div>
</section>
<div class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-16">
    <h2 class="text-3xl font-bold text-gray-900">Environmental Impact</h2>
    <p class="text-gray-600 mt-4">Making parking sustainable for future generations</p>
    </div>
    
    <div class="grid md:grid-cols-3 gap-8">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
    <i class="ri-leaf-line text-primary text-3xl"></i>
    </div>
    <h3 class="text-4xl font-bold text-primary mb-2">30%</h3>
    <p class="text-gray-600">Reduction in CO2 Emissions</p>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
    <i class="ri-lightbulb-line text-primary text-3xl"></i>
    </div>
    <h3 class="text-4xl font-bold text-primary mb-2">45%</h3>
    <p class="text-gray-600">Energy Efficiency Improvement</p>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
    <i class="ri-recycle-line text-primary text-3xl"></i>
    </div>
    <h3 class="text-4xl font-bold text-primary mb-2">25%</h3>
    <p class="text-gray-600">Waste Reduction</p>
    </div>
    </div>
    </div>
    </div>
    
    <div class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-16">
    <h2 class="text-3xl font-bold text-gray-900">Customer Testimonials</h2>
    <p class="text-gray-600 mt-4">What our clients say about us</p>
    </div>
    
    <div class="relative">
    <div class="flex overflow-x-hidden" id="testimonialSlider">
    <div class="flex transition-transform duration-300 ease-in-out" id="testimonialWrapper">
    <div class="w-full md:w-1/3 flex-shrink-0 px-4">
    <div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="flex items-center mb-4">
    <img src="https://public.readdy.ai/ai/img_res/b96f0374dec83a715ea4de80ab8b7f51.jpg" class="w-12 h-12 rounded-full object-cover" alt="Customer">
    <div class="ml-4">
    <h4 class="font-bold">Michael Anderson</h4>
    <p class="text-gray-600">City Fleet Manager</p>
    </div>
    </div>
    <p class="text-gray-600">"SmartPark has transformed how we manage our fleet parking. The efficiency gains are remarkable."</p>
    <div class="flex text-primary mt-4">
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    </div>
    </div>
    </div>
    
    <div class="w-full md:w-1/3 flex-shrink-0 px-4">
    <div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="flex items-center mb-4">
    <img src="https://public.readdy.ai/ai/img_res/89f190f46b8e0ab6dee24d01889c548a.jpg" class="w-12 h-12 rounded-full object-cover" alt="Customer">
    <div class="ml-4">
    <h4 class="font-bold">Sarah Martinez</h4>
    <p class="text-gray-600">Property Manager</p>
    </div>
    </div>
    <p class="text-gray-600">"The real-time monitoring and automated payment systems have significantly improved our operations."</p>
    <div class="flex text-primary mt-4">
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    </div>
    </div>
    </div>
    
    <div class="w-full md:w-1/3 flex-shrink-0 px-4">
    <div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="flex items-center mb-4">
    <img src="https://public.readdy.ai/ai/img_res/d351adfcf1e711ebf6f1f1bd123cd293.jpg" class="w-12 h-12 rounded-full object-cover" alt="Customer">
    <div class="ml-4">
    <h4 class="font-bold">David Chen</h4>
    <p class="text-gray-600">Mall Operations Director</p>
    </div>
    </div>
    <p class="text-gray-600">"Customer satisfaction has increased dramatically since implementing SmartPark's solution."</p>
    <div class="flex text-primary mt-4">
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-fill"></i>
    <i class="ri-star-half-fill"></i>
    </div>
    </div>
    </div>
    </div>
    </div>
    
    <button class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-lg" onclick="slideTestimonials('prev')">
    <i class="ri-arrow-left-s-line text-2xl text-primary"></i>
    </button>
    <button class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-lg" onclick="slideTestimonials('next')">
    <i class="ri-arrow-right-s-line text-2xl text-primary"></i>
    </button>
    </div>
    </div>
    </div>
    
    <div class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-16">
    <h2 class="text-3xl font-bold text-gray-900">Frequently Asked Questions</h2>
    <p class="text-gray-600 mt-4">Find answers to common questions about our services</p>
    </div>
    
    <div class="max-w-3xl mx-auto space-y-4" id="faqContainer">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <button class="w-full px-6 py-4 text-left flex justify-between items-center" onclick="toggleFaq(0)">
    <span class="font-semibold">How does the smart parking system work?</span>
    <i class="ri-arrow-down-s-line text-xl transition-transform"></i>
    </button>
    <div class="px-6 py-4 border-t border-gray-100 hidden">
    <p class="text-gray-600">Our smart parking system uses IoT sensors and AI technology to monitor parking spaces in real-time. Users can view available spots through our mobile app and make reservations instantly.</p>
    </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <button class="w-full px-6 py-4 text-left flex justify-between items-center" onclick="toggleFaq(1)">
    <span class="font-semibold">What payment methods are accepted?</span>
    <i class="ri-arrow-down-s-line text-xl transition-transform"></i>
    </button>
    <div class="px-6 py-4 border-t border-gray-100 hidden">
    <p class="text-gray-600">We accept all major credit cards, digital wallets including Apple Pay and Google Pay, and our own SmartPark mobile payment system.</p>
    </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <button class="w-full px-6 py-4 text-left flex justify-between items-center" onclick="toggleFaq(2)">
    <span class="font-semibold">Is the system available 24/7?</span>
    <i class="ri-arrow-down-s-line text-xl transition-transform"></i>
    </button>
    <div class="px-6 py-4 border-t border-gray-100 hidden">
    <p class="text-gray-600">Yes, our smart parking system operates 24/7 with continuous monitoring and customer support available at all times.</p>
    </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <button class="w-full px-6 py-4 text-left flex justify-between items-center" onclick="toggleFaq(3)">
    <span class="font-semibold">How secure is the parking facility?</span>
    <i class="ri-arrow-down-s-line text-xl transition-transform"></i>
    </button>
    <div class="px-6 py-4 border-t border-gray-100 hidden">
    <p class="text-gray-600">Our facilities are equipped with 24/7 video surveillance, emergency response systems, and regular security patrols to ensure the safety of your vehicle.</p>
    </div>
    </div>
    </div>
    </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
showCookieConsent();
initParkingTrends();
});

let currentSlide = 0;
const slideCount = 3;

function slideTestimonials(direction) {
if (direction === 'next') {
currentSlide = (currentSlide + 1) % slideCount;
} else {
currentSlide = (currentSlide - 1 + slideCount) % slideCount;
}
const wrapper = document.getElementById('testimonialWrapper');
wrapper.style.transform = `translateX(-${currentSlide * 33.333}%)`;
}
function toggleFaq(index) {
const faqContainer = document.getElementById('faqContainer');
const faqItems = faqContainer.children;
const content = faqItems[index].querySelector('div');
const arrow = faqItems[index].querySelector('i');
content.classList.toggle('hidden');
arrow.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
}

document.addEventListener('DOMContentLoaded', () => {
initCharts();

window.addEventListener('resize', () => {
const charts = ['iotChart', 'cloudChart', 'aiChart'];
charts.forEach(chartId => {
const chart = echarts.getInstanceByDom(document.getElementById(chartId));
if (chart) {
chart.resize();
}
});
});
});
function initCharts() {
const chartOptions = {
iotChart: {
title: 'Sensor Network Growth',
data: [30, 45, 62, 78, 90]
},
cloudChart: {
title: 'System Uptime',
data: [99.1, 99.3, 99.5, 99.7, 99.9]
},
aiChart: {
title: 'Prediction Accuracy',
data: [85, 88, 91, 94, 96]
}
};

Object.entries(chartOptions).forEach(([chartId, config]) => {
const chart = echarts.init(document.getElementById(chartId));
const option = {
animation: false,
title: {
text: config.title,
textStyle: {
fontSize: 14,
fontWeight: 'normal',
color: '#666'
},
left: 'center',
top: 0
},
grid: {
top: 30,
right: 20,
bottom: 20,
left: 20,
containLabel: true
},
xAxis: {
type: 'category',
data: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
axisLine: { show: false },
axisTick: { show: false }
},
yAxis: {
type: 'value',
axisLine: { show: false },
axisTick: { show: false },
splitLine: { show: true, lineStyle: { color: '#f0f0f0' } }
},
series: [{
data: config.data,
type: 'line',
smooth: true,
symbol: 'none',
lineStyle: { color: 'rgba(87, 181, 231, 1)' },
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
color: 'rgba(87, 181, 231, 0.05)'
}]
}
}
}]
};
chart.setOption(option);
});
}

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
function toggleFaq(index) {
const faqContainer = document.getElementById('faqContainer');
const faqItems = faqContainer.children;
const content = faqItems[index].querySelector('div');
const arrow = faqItems[index].querySelector('i');
content.classList.toggle('hidden');
arrow.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
}
function showJobDetails(position) {
const positions = {
'software-engineer': {
title: 'Software Engineer',
description: 'We are looking for a talented Software Engineer to join our team.',
requirements: ['5+ years of experience', 'Full-stack development', 'Cloud technologies']
},
'product-designer': {
title: 'Product Designer',
description: 'Join us as a Product Designer to create amazing user experiences.',
requirements: ['3+ years of experience', 'UI/UX expertise', 'Prototyping skills']
},
'business-analyst': {
title: 'Business Analyst',
description: 'Help us make data-driven decisions as a Business Analyst.',
requirements: ['3+ years of experience', 'Data analysis', 'Business strategy']
}
};
const position_details = positions[position];
const modal = document.createElement('div');
modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
modal.innerHTML = `
<div class="bg-white rounded-lg p-8 max-w-md w-full">
<div class="flex justify-between items-center mb-6">
<h3 class="text-2xl font-semibold">${position_details.title}</h3>
<button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
<i class="ri-close-line text-2xl"></i>
</button>
</div>
<div class="space-y-4">
<p class="text-gray-600">${position_details.description}</p>
<div>
<h4 class="font-semibold mb-2">Requirements:</h4>
<ul class="list-disc list-inside text-gray-600">
${position_details.requirements.map(req => `<li>${req}</li>`).join('')}
</ul>
</div>
<button onclick="applyForPosition('${position}')" class="w-full bg-primary text-white py-2 !rounded-button cursor-pointer whitespace-nowrap">Apply Now</button>
</div>
</div>
`;
document.body.appendChild(modal);
}
function applyForPosition(position) {
showToast('Application submitted successfully');
const modal = document.querySelector('.fixed.inset-0');
if (modal) modal.remove();
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