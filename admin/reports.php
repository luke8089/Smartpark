<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reglogin.php");
    exit();
}
// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../components/function.php';
$conn = db_connect();

// Enhanced statistics
$total_users = $conn->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetch_row()[0];
$total_admins = $conn->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetch_row()[0];
$total_spots = $conn->query('SELECT COUNT(*) FROM parking_spots')->fetch_row()[0];
$total_reservations = $conn->query('SELECT COUNT(*) FROM reservations')->fetch_row()[0];
$total_mpesa_payments = $conn->query('SELECT SUM(amount) FROM mpesa_payments WHERE status = "completed"')->fetch_row()[0] ?? 0;
$total_equity_payments = $conn->query('SELECT SUM(amount) FROM equity_payments WHERE status = "completed"')->fetch_row()[0] ?? 0;
$total_revenue = $total_mpesa_payments + $total_equity_payments;

// Get daily reservations for last 7 days
$daily_reservations = $conn->query("SELECT DATE(created_at) as day, COUNT(*) as count FROM reservations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY day ORDER BY day DESC")->fetch_all(MYSQLI_ASSOC);

// Get monthly revenue for last 6 months
$monthly_revenue = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(amount) as revenue
    FROM mpesa_payments 
    WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month 
    ORDER BY month DESC
")->fetch_all(MYSQLI_ASSOC);

// Get top parking spots by reservations
$top_spots = $conn->query("
    SELECT 
        ps.name,
        ps.location,
        COUNT(r.id) as reservation_count,
        SUM(r.fee) as total_revenue
    FROM parking_spots ps
    LEFT JOIN reservations r ON ps.id = r.spot_id
    GROUP BY ps.id
    ORDER BY reservation_count DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get recent activities
$recent_activities = $conn->query("
    SELECT 
        'reservation' as type,
        r.booking_ref as reference,
        CONCAT(u.name, ' booked ', ps.name) as description,
        r.created_at as timestamp
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN parking_spots ps ON r.spot_id = ps.id
    UNION ALL
    SELECT 
        'payment' as type,
        mp.mpesa_ref as reference,
        CONCAT('Payment of Ksh', mp.amount, ' by ', u.name) as description,
        mp.created_at as timestamp
    FROM mpesa_payments mp
    JOIN users u ON mp.user_id = u.id
    WHERE mp.status = 'completed'
    ORDER BY timestamp DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - SmartPark Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#20215B',
            secondary: '#64748B',
            success: '#10B981',
            warning: '#F59E0B',
            danger: '#EF4444'
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
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    .fade-in { opacity: 0; transform: translateY(30px) scale(0.98); animation: fadeInUp 0.7s cubic-bezier(.4,2,.6,1) forwards; }
    .slide-in { opacity: 0; transform: translateX(-30px); animation: slideInLeft 0.6s cubic-bezier(.4,2,.6,1) forwards; }
    .bounce-in { opacity: 0; transform: scale(0.8); animation: bounceIn 0.8s cubic-bezier(.4,2,.6,1) forwards; }
    @keyframes fadeInUp { to { opacity: 1; transform: none; } }
    @keyframes slideInLeft { to { opacity: 1; transform: none; } }
    @keyframes bounceIn { 0% { transform: scale(0.3); opacity: 0; } 50% { transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { transform: scale(1); opacity: 1; } }
    .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .card-hover { transition: all 0.3s ease; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
  </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
<?php include 'includes/header.php'; ?>
<div class="flex flex-1 flex-row gap-x-8 min-w-0 w-full">
  <?php include 'includes/sidebar.php'; ?>
  <main class="flex-1 min-w-0 w-full flex flex-col p-6 lg:p-8">
    <!-- Header Section -->
    <div class="mb-8 fade-in">
      <h1 class="text-4xl font-bold font-['Pacifico'] text-primary mb-2">Analytics Dashboard</h1>
      <p class="text-gray-600">Comprehensive overview of SmartPark performance and insights</p>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-2xl shadow-lg p-6 card-hover bounce-in" style="animation-delay:0.1s">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Users</p>
            <p class="text-3xl font-bold text-primary"><?php echo number_format($total_users); ?></p>
            <p class="text-xs text-gray-500 mt-1"><?php echo $total_admins; ?> admins included</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-full">
            <i class="ri-user-3-line text-2xl text-primary"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-lg p-6 card-hover bounce-in" style="animation-delay:0.2s">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Parking Spots</p>
            <p class="text-3xl font-bold text-primary"><?php echo number_format($total_spots); ?></p>
            <p class="text-xs text-gray-500 mt-1">Available locations</p>
          </div>
          <div class="bg-green-100 p-3 rounded-full">
            <i class="ri-parking-line text-2xl text-success"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-lg p-6 card-hover bounce-in" style="animation-delay:0.3s">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Reservations</p>
            <p class="text-3xl font-bold text-primary"><?php echo number_format($total_reservations); ?></p>
            <p class="text-xs text-gray-500 mt-1">Bookings made</p>
          </div>
          <div class="bg-purple-100 p-3 rounded-full">
            <i class="ri-calendar-check-line text-2xl text-warning"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-lg p-6 card-hover bounce-in" style="animation-delay:0.4s">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
            <p class="text-3xl font-bold text-primary">Ksh<?php echo number_format($total_revenue, 2); ?></p>
            <p class="text-xs text-gray-500 mt-1">All time earnings</p>
          </div>
          <div class="bg-yellow-100 p-3 rounded-full">
            <i class="ri-bank-card-line text-2xl text-warning"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->

    <!-- Additional Data Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Top Parking Spots -->
      <div class="bg-white rounded-2xl shadow-lg p-6 slide-in" style="animation-delay:0.7s">
        <h3 class="text-xl font-bold text-primary mb-4 flex items-center">
          <i class="ri-star-line mr-2"></i>Top Parking Spots
        </h3>
        <div class="space-y-4">
          <?php foreach ($top_spots as $index => $spot): ?>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">
                <?php echo $index + 1; ?>
              </div>
              <div>
                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($spot['name']); ?></p>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($spot['location']); ?></p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-bold text-primary"><?php echo $spot['reservation_count']; ?></p>
              <p class="text-xs text-gray-500">Ksh<?php echo number_format($spot['total_revenue'], 2); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="bg-white rounded-2xl shadow-lg p-6 slide-in" style="animation-delay:0.8s">
        <h3 class="text-xl font-bold text-primary mb-4 flex items-center">
          <i class="ri-time-line mr-2"></i>Recent Activities
        </h3>
        <div class="space-y-4 max-h-80 overflow-y-auto">
          <?php foreach ($recent_activities as $activity): ?>
          <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
            <div class="w-2 h-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($activity['description']); ?></p>
              <p class="text-xs text-gray-500"><?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?></p>
            </div>
          </div>
            <?php endforeach; ?>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="bg-white rounded-2xl shadow-lg p-6 slide-in" style="animation-delay:0.9s">
        <h3 class="text-xl font-bold text-primary mb-4 flex items-center">
          <i class="ri-bar-chart-line mr-2"></i>Quick Stats
        </h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
            <div class="flex items-center">
              <i class="ri-money-dollar-circle-line text-blue-500 mr-3"></i>
              <span class="font-medium">M-Pesa Revenue</span>
            </div>
            <span class="font-bold text-blue-600">Ksh<?php echo number_format($total_mpesa_payments, 2); ?></span>
          </div>
          
          <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
            <div class="flex items-center">
              <i class="ri-bank-line text-green-500 mr-3"></i>
              <span class="font-medium">Equity Revenue</span>
            </div>
            <span class="font-bold text-green-600">Ksh<?php echo number_format($total_equity_payments, 2); ?></span>
          </div>
          
          <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
            <div class="flex items-center">
              <i class="ri-user-star-line text-purple-500 mr-3"></i>
              <span class="font-medium">Admin Users</span>
            </div>
            <span class="font-bold text-purple-600"><?php echo $total_admins; ?></span>
          </div>
          
          <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
            <div class="flex items-center">
              <i class="ri-calendar-line text-orange-500 mr-3"></i>
              <span class="font-medium">Today's Date</span>
            </div>
            <span class="font-bold text-orange-600"><?php echo date('M j, Y'); ?></span>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Reservations Chart

});
</script>
</body>
</html>
