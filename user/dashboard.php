<?php session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ../reglogin.php');
  exit();
}
// Prevent page caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

require_once '../components/function.php';

// Get pending equity payments for the user
$user_id = $_SESSION['user']['id'];
$conn = db_connect();
$stmt = $conn->prepare('
    SELECT r.*, s.name as spot_name, ep.status as payment_status, ep.transaction_ref, ep.created_at as payment_date
    FROM reservations r 
    JOIN parking_spots s ON r.spot_id = s.id
    LEFT JOIN equity_payments ep ON r.id = ep.reservation_id
    WHERE r.user_id = ? AND r.payment_method = "equity"
    ORDER BY ep.created_at DESC
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_equity_payments = [];
while ($row = $result->fetch_assoc()) {
    if ($row['payment_status'] && $row['payment_status'] !== 'completed') {
        $pending_equity_payments[] = $row;
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SmartPark Dashboard</title>
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

<div class="flex h-screen">
<aside class="w-64 bg-white shadow-lg">
<div class="p-6 border-b">
<a href="#" class="text-2xl font-['Pacifico'] text-primary">SmartPark Innovations</a>
</div>
<nav class="p-4 space-y-2">
<div class="flex items-center space-x-3 p-3 bg-primary/10 text-primary rounded-lg">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-dashboard-line"></i>
</div>
<span>Dashboard</span>
</div>
<a href="index.php" data-readdy="true" class="flex items-center space-x-3 p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-home-line"></i>
</div>
<span>Home</span>
</a>
<button onclick="showReservations()" class="w-full flex items-center space-x-3 p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-time-line"></i>
</div>
<span>Active Reservations</span>
</button>
<button onclick="showHistory()" class="w-full flex items-center space-x-3 p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-history-line"></i>
</div>
<span>Parking History</span>
</button>
<button onclick="showLocations()" class="w-full flex items-center space-x-3 p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-map-pin-line"></i>
</div>
<span>Saved Locations</span>
</button>
<button onclick="showVehicles()" class="w-full flex items-center space-x-3 p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-car-line"></i>
</div>
<span>Vehicles</span>
</button>
<button onclick="showPayments()" class="w-full flex items-center space-x-3 p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-bank-card-line"></i>
</div>
<span>Payment Methods</span>
</button>
<button onclick="showSettings()" class="w-full flex items-center space-x-3 p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-settings-line"></i>
</div>
<span>Settings</span>
</button>
</nav>
</aside>

<main class="flex-1 overflow-auto">
<?php include 'includes/header.php'; ?>

<div class="p-8 space-y-8">
<div class="grid grid-cols-4 gap-6">
<div class="bg-white p-6 rounded-lg shadow">
<div class="flex items-center justify-between">
<h3 class="text-gray-500">Active Reservations</h3>
<div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
<i class="ri-parking-line text-primary"></i>
</div>
</div>
<p class="text-3xl font-semibold mt-2">2</p>
</div>
<div class="bg-white p-6 rounded-lg shadow">
<div class="flex items-center justify-between">
<h3 class="text-gray-500">Total Spent</h3>
<div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
<i class="ri-money-dollar-circle-line text-primary"></i>
</div>
</div>
<p class="text-3xl font-semibold mt-2">$324.50</p>
</div>
<div class="bg-white p-6 rounded-lg shadow">
<div class="flex items-center justify-between">
<h3 class="text-gray-500">Saved Locations</h3>
<div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
<i class="ri-map-pin-line text-primary"></i>
</div>
</div>
<p class="text-3xl font-semibold mt-2">5</p>
</div>
<div class="bg-white p-6 rounded-lg shadow">
<div class="flex items-center justify-between">
<h3 class="text-gray-500">Vehicles</h3>
<div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
<i class="ri-car-line text-primary"></i>
</div>
</div>
<p class="text-3xl font-semibold mt-2">2</p>
</div>
</div>

<div class="grid grid-cols-3 gap-6">
<div class="col-span-2 bg-white rounded-lg shadow">
<div class="p-6 border-b">
<h2 class="text-xl font-semibold">Available Parking Spots</h2>
</div>
<div class="relative h-[400px]">
<img src="https://public.readdy.ai/gen_page/map_placeholder_1280x720.png" alt="Parking Map" class="absolute inset-0 w-full h-full object-cover">
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

<div class="bg-white rounded-lg shadow">
<div class="p-6 border-b">
<h2 class="text-xl font-semibold">Active Reservations</h2>
</div>
<div class="p-6 space-y-4">
<div class="p-4 border rounded-lg">
<div class="flex items-center justify-between mb-2">
<h3 class="font-medium">Central Business District</h3>
<span class="text-green-500 text-sm">Active</span>
</div>
<div class="text-sm text-gray-500 space-y-1">
<p>Spot: A-123</p>
<p>Until: 2:30 PM Today</p>
</div>
<div class="mt-4 flex space-x-2">
<button onclick="extendReservation()" class="flex-1 bg-primary text-white py-2 !rounded-button text-sm">Extend</button>
<button onclick="cancelReservation()" class="flex-1 border border-red-500 text-red-500 py-2 !rounded-button text-sm">Cancel</button>
</div>
</div>

<div class="p-4 border rounded-lg">
<div class="flex items-center justify-between mb-2">
<h3 class="font-medium">West Side Mall</h3>
<span class="text-blue-500 text-sm">Upcoming</span>
</div>
<div class="text-sm text-gray-500 space-y-1">
<p>Spot: B-45</p>
<p>Starts: 4:00 PM Today</p>
</div>
<div class="mt-4">
<button onclick="cancelReservation()" class="w-full border border-red-500 text-red-500 py-2 !rounded-button text-sm">Cancel</button>
</div>
</div>
</div>
</div>
</div>

<?php if (!empty($pending_equity_payments)): ?>
<div class="bg-white rounded-lg shadow mt-6">
<div class="p-6 border-b">
<h2 class="text-xl font-semibold flex items-center">
<i class="ri-time-line text-yellow-600 mr-2"></i>
Pending Equity Payments
</h2>
</div>
<div class="p-6 space-y-4">
<?php foreach ($pending_equity_payments as $payment): ?>
<div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
<div class="flex items-center justify-between mb-2">
<h3 class="font-medium"><?php echo htmlspecialchars($payment['spot_name']); ?></h3>
<span class="text-yellow-600 text-sm font-medium">
<?php echo ucfirst($payment['payment_status']); ?>
</span>
</div>
<div class="text-sm text-gray-600 space-y-1">
<p><strong>Booking Ref:</strong> <?php echo htmlspecialchars($payment['booking_ref']); ?></p>
<p><strong>Vehicle:</strong> <?php echo htmlspecialchars($payment['license_plate'] . ' - ' . $payment['vehicle_type']); ?></p>
<p><strong>Amount:</strong> Ksh<?php echo number_format($payment['fee'], 2); ?></p>
<p><strong>Submitted:</strong> <?php echo date('Y-m-d H:i', strtotime($payment['payment_date'])); ?></p>
<?php if ($payment['transaction_ref']): ?>
<p><strong>Transaction Ref:</strong> <code class="bg-gray-200 px-1 rounded text-xs"><?php echo htmlspecialchars($payment['transaction_ref']); ?></code></p>
<?php endif; ?>
</div>
<div class="mt-3 p-2 bg-yellow-100 rounded text-xs text-yellow-800">
<i class="ri-information-line mr-1"></i>
Payment is pending admin confirmation. You will receive notification once verified.
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<div class="grid grid-cols-2 gap-6">
<div class="bg-white rounded-lg shadow">
<div class="p-6 border-b">
<h2 class="text-xl font-semibold">Recent Activity</h2>
</div>
<div class="p-6">
<div id="activityChart" class="h-[300px]"></div>
</div>
</div>

<div class="bg-white rounded-lg shadow">
<div class="p-6 border-b">
<h2 class="text-xl font-semibold">Parking History</h2>
</div>
<div class="p-6">
<div class="space-y-4">
<div class="flex items-center justify-between">
<div>
<h3 class="font-medium">Downtown Plaza</h3>
<p class="text-sm text-gray-500">March 5, 2025</p>
</div>
<span class="text-primary font-medium">$12.50</span>
</div>
<div class="flex items-center justify-between">
<div>
<h3 class="font-medium">Airport Terminal B</h3>
<p class="text-sm text-gray-500">March 3, 2025</p>
</div>
<span class="text-primary font-medium">$28.00</span>
</div>
<div class="flex items-center justify-between">
<div>
<h3 class="font-medium">Shopping Center</h3>
<p class="text-sm text-gray-500">March 1, 2025</p>
</div>
<span class="text-primary font-medium">$8.75</span>
</div>
</div>
<button onclick="showAllHistory()" class="w-full mt-6 text-primary text-sm font-medium">View All History</button>
</div>
</div>
</div>
</div>
</main>
<?php include 'includes/footer.php'; ?>
</div>

<div id="profileMenu" class="hidden absolute top-20 right-8 w-48 bg-white rounded-lg shadow-lg py-2">
<a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
<a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
<a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Help</a>
<hr class="my-2">
<a href="#" class="block px-4 py-2 text-red-500 hover:bg-gray-100">Logout</a>
</div>

<div id="toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg toast"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
initActivityChart();
});

function initActivityChart() {
const chart = echarts.init(document.getElementById('activityChart'));
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
data: [30, 45, 35, 50, 40, 35, 45],
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

function toggleProfileMenu() {
const menu = document.getElementById('profileMenu');
menu.classList.toggle('hidden');
}

function showToast(message) {
const toast = document.getElementById('toast');
toast.textContent = message;
toast.classList.add('show');
setTimeout(() => {
toast.classList.remove('show');
}, 3000);
}

function extendReservation() {
showToast('Reservation extended successfully');
}

function cancelReservation() {
showToast('Reservation cancelled successfully');
}

function showNotifications() {
showToast('No new notifications');
}

function showReservations() {
showToast('Loading reservations...');
}

function showHistory() {
showToast('Loading parking history...');
}

function showLocations() {
showToast('Loading saved locations...');
}

function showVehicles() {
showToast('Loading vehicles...');
}

function showPayments() {
showToast('Loading payment methods...');
}

function showSettings() {
showToast('Loading settings...');
}

function showAllHistory() {
showToast('Loading complete history...');
}
</script>

</body>
</html>
