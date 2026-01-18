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
// Fetch stats
$conn = db_connect();
$total_users = $conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
$total_spots = $conn->query('SELECT COUNT(*) FROM parking_spots')->fetch_row()[0];
$total_reservations = $conn->query('SELECT COUNT(*) FROM reservations')->fetch_row()[0];
$total_payments = $conn->query('SELECT SUM(amount) FROM mpesa_payments')->fetch_row()[0] ?? 0;
// Fetch recent reservations
$recent_reservations = $conn->query('SELECT r.id, u.name as user_name, s.name as spot_name, r.license_plate, r.entry_time, r.exit_time, r.fee FROM reservations r JOIN users u ON r.user_id = u.id JOIN parking_spots s ON r.spot_id = s.id ORDER BY r.created_at DESC LIMIT 5')->fetch_all(MYSQLI_ASSOC);
// Fetch recent payments
$recent_payments = $conn->query('SELECT m.id, u.name as user_name, m.amount, m.status, m.created_at FROM mpesa_payments m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC LIMIT 5')->fetch_all(MYSQLI_ASSOC);
// Fetch recent users
$conn = db_connect();
$recent_users = $conn->query('SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5')->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - SmartPark</title>
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
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include 'includes/header.php'; ?>
<div class="flex flex-1 flex-row gap-x-8 min-w-0 w-full flex-wrap">
  <?php include 'includes/sidebar.php'; ?>
  <main class="flex-1 min-w-0 w-full flex flex-col p-4 sm:p-8">
    <h1 class="text-3xl font-bold mb-8 font-['Pacifico'] text-primary fade-in">Admin Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center fade-in" style="animation-delay:0.1s">
          <i class="ri-user-3-line text-4xl text-primary mb-4 animate-bounce"></i>
          <div class="text-3xl font-bold mb-2" id="stat-users">0</div>
          <div class="text-gray-500">Total Users</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center fade-in" style="animation-delay:0.2s">
          <i class="ri-parking-line text-4xl text-primary mb-4 animate-bounce"></i>
          <div class="text-3xl font-bold mb-2" id="stat-spots">0</div>
          <div class="text-gray-500">Parking Spots</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center fade-in" style="animation-delay:0.3s">
          <i class="ri-calendar-check-line text-4xl text-primary mb-4 animate-bounce"></i>
          <div class="text-3xl font-bold mb-2" id="stat-reservations">0</div>
          <div class="text-gray-500">Reservations</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center fade-in" style="animation-delay:0.4s">
          <i class="ri-bank-card-line text-4xl text-primary mb-4 animate-bounce"></i>
          <div class="text-3xl font-bold mb-2" id="stat-payments">0</div>
          <div class="text-gray-500">Total Payments (Ksh)</div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">
        <div class="bg-white rounded-xl shadow-lg p-6 fade-in w-full max-w-3xl mx-auto" style="animation-delay:0.5s">
          <h2 class="text-xl font-bold text-primary mb-4 flex items-center"><i class="ri-calendar-check-line mr-2"></i>Recent Reservations</h2>
          <div>
            <table class="min-w-full w-full text-sm">
              <thead>
                <tr class="bg-primary text-white">
                  <th class="px-4 py-2 text-left">User</th>
                  <th class="px-4 py-2 text-left">Spot</th>
                  <th class="px-4 py-2 text-left">Plate</th>
                  <th class="px-4 py-2 text-left">Entry</th>
                  <th class="px-4 py-2 text-left">Exit</th>
                  <th class="px-4 py-2 text-right">Fee</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_reservations as $r): ?>
                <tr class="border-b hover:bg-primary/5 transition">
                  <td class="px-4 py-2"><?php echo htmlspecialchars($r['user_name']); ?></td>
                  <td class="px-4 py-2"><?php echo htmlspecialchars($r['spot_name']); ?></td>
                  <td class="px-4 py-2"><?php echo htmlspecialchars($r['license_plate']); ?></td>
                  <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($r['entry_time'])); ?></td>
                  <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($r['exit_time'])); ?></td>
                  <td class="px-4 py-2 text-right text-primary font-semibold">Ksh<?php echo number_format($r['fee'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="flex justify-end mt-4">
            <a href="reservations.php" class="bg-primary text-white px-6 py-2 rounded-button font-semibold shadow hover:bg-primary/90 transition">More</a>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 fade-in" style="animation-delay:0.6s">
          <h2 class="text-xl font-bold text-primary mb-4 flex items-center"><i class="ri-bank-card-line mr-2"></i>Recent Payments</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="bg-primary text-white">
                  <th class="px-4 py-2 text-left">User</th>
                  <th class="px-4 py-2 text-left">Amount</th>
                  <th class="px-4 py-2 text-left">Status</th>
                  <th class="px-4 py-2 text-left">Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_payments as $p): ?>
                <tr class="border-b hover:bg-primary/5 transition">
                  <td class="px-4 py-2"><?php echo htmlspecialchars($p['user_name']); ?></td>
                  <td class="px-4 py-2 text-primary font-semibold">Ksh<?php echo number_format($p['amount'], 2); ?></td>
                  <td class="px-4 py-2"><?php echo ucfirst(htmlspecialchars($p['status'])); ?></td>
                  <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($p['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="flex justify-end mt-4">
            <a href="payments.php" class="bg-primary text-white px-6 py-2 rounded-button font-semibold shadow hover:bg-primary/90 transition">More</a>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">
        <div class="bg-white rounded-xl shadow-lg p-6 fade-in w-full max-w-3xl mx-auto" style="animation-delay:0.7s">
          <h2 class="text-xl font-bold text-primary mb-4 flex items-center"><i class="ri-user-3-line mr-2"></i>Recent Users</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="bg-primary text-white">
                  <th class="px-4 py-2 text-left">Name</th>
                  <th class="px-4 py-2 text-left">Email</th>
                  <th class="px-4 py-2 text-left">Role</th>
                  <th class="px-4 py-2 text-left">Registered</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_users as $u): ?>
                <tr class="border-b hover:bg-primary/5 transition">
                  <td class="px-4 py-2"><?php echo htmlspecialchars($u['name']); ?></td>
                  <td class="px-4 py-2"><?php echo htmlspecialchars($u['email']); ?></td>
                  <td class="px-4 py-2"><?php echo ucfirst(htmlspecialchars($u['role'])); ?></td>
                  <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($u['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="flex justify-end mt-4">
            <a href="payments.php" class="bg-primary text-white px-6 py-2 rounded-button font-semibold shadow hover:bg-primary/90 transition">More</a>
          </div>
        </div>
        
      </div>
      <!-- Add more dashboard content here -->
    </main>
</div>
<?php include 'includes/footer.php'; ?>
<script>
function animateStat(id, end, duration = 1200) {
  const el = document.getElementById(id);
  let start = 0;
  const step = Math.ceil(end / (duration / 16));
  function update() {
    start += step;
    if (start >= end) {
      el.textContent = end.toLocaleString();
    } else {
      el.textContent = start.toLocaleString();
      requestAnimationFrame(update);
    }
  }
  update();
}
document.addEventListener('DOMContentLoaded', function() {
  animateStat('stat-users', <?php echo (int)$total_users; ?>);
  animateStat('stat-spots', <?php echo (int)$total_spots; ?>);
  animateStat('stat-reservations', <?php echo (int)$total_reservations; ?>);
  animateStat('stat-payments', <?php echo (int)$total_payments; ?>);
  document.querySelectorAll('.fade-in').forEach((el, i) => {
    el.style.animationDelay = (i * 0.1 + 0.1) + 's';
  });
});
</script>
</body>
</html>
