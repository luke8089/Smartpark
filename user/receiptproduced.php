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
require_once '../components/function.php';
$user_id = $_SESSION['user']['id'];
$conn = db_connect();

// Get all reservations with payment status information
$stmt = $conn->prepare('
    SELECT r.*, s.name AS spot_name,
           CASE 
               WHEN r.payment_method = "equity" THEN ep.status
               WHEN r.payment_method = "mpesa" THEN mp.status
               ELSE "completed"
           END as payment_status
    FROM reservations r 
    JOIN parking_spots s ON r.spot_id = s.id 
    LEFT JOIN equity_payments ep ON r.id = ep.reservation_id
    LEFT JOIN mpesa_payments mp ON r.id = mp.reservation_id
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$receipts = [];
while ($row = $result->fetch_assoc()) {
  // Only include receipts for completed payments or non-equity payments
  if ($row['payment_method'] !== 'equity' || $row['payment_status'] === 'completed') {
    $receipts[] = $row;
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
  <title>My Receipts - SmartPark Innovations</title>
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
<main class="max-w-5xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-8 text-center font-['Pacifico'] text-primary">My Receipts</h1>
  <?php if (empty($receipts)): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500 text-lg animate-pulse">
      <i class="ri-file-list-3-line text-4xl mb-2 text-primary"></i><br>
      No receipts found. Book a parking spot to get started!<br>
      <span class="text-sm text-gray-400 mt-2">Note: Equity payments will appear here once confirmed by admin.</span>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <?php foreach ($receipts as $i => $r): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 border border-primary/20 fade-in" style="animation-delay: <?php echo ($i * 0.1); ?>s">
          <div class="flex items-center justify-between mb-4">
            <span class="text-primary font-semibold text-lg flex items-center"><i class="ri-receipt-2-line mr-2"></i>Receipt</span>
            <span class="text-xs bg-primary/10 text-primary px-3 py-1 rounded-full">#<?php echo htmlspecialchars($r['booking_ref']); ?></span>
          </div>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span class="font-medium">Parking Spot:</span> <span><?php echo htmlspecialchars($r['spot_name']) . ' (Spot #' . htmlspecialchars($r['spot_number']) . ')'; ?></span></div>
            <div class="flex justify-between"><span class="font-medium">License Plate:</span> <span><?php echo htmlspecialchars($r['license_plate']); ?></span></div>
            <div class="flex justify-between"><span class="font-medium">Vehicle:</span> <span><?php echo htmlspecialchars($r['vehicle_type'] . ' - ' . $r['vehicle_model']); ?></span></div>
            <div class="flex justify-between"><span class="font-medium">Entry:</span> <span><?php echo date('Y-m-d H:i', strtotime($r['entry_time'])); ?></span></div>
            <div class="flex justify-between"><span class="font-medium">Exit:</span> <span><?php echo date('Y-m-d H:i', strtotime($r['exit_time'])); ?></span></div>
            <?php
            // Determine payment method and status for this receipt
            $payment_status = 'pending';
            $status_color = 'text-yellow-600';
            $status_bg = 'bg-yellow-100';
            $actual_payment_method = 'Pending';
            $conn = db_connect();
            // Check for equity payments first
            $stmt = $conn->prepare("SELECT status FROM equity_payments WHERE reservation_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param('i', $r['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $equity_payment = $result->fetch_assoc();
            $stmt->close();
            // Check for mpesa payments
            $stmt = $conn->prepare("SELECT status FROM mpesa_payments WHERE reservation_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param('i', $r['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $mpesa_payment = $result->fetch_assoc();
            $stmt->close();
            $conn->close();
            if ($equity_payment) {
              $actual_payment_method = 'Equity';
              switch ($equity_payment['status']) {
                case 'pending':
                  $payment_status = 'pending';
                  $status_color = 'text-yellow-600';
                  $status_bg = 'bg-yellow-100';
                  break;
                case 'completed':
                  $payment_status = 'confirmed';
                  $status_color = 'text-green-600';
                  $status_bg = 'bg-green-100';
                  break;
                case 'failed':
                case 'cancelled':
                  $payment_status = 'cancelled';
                  $status_color = 'text-red-600';
                  $status_bg = 'bg-red-100';
                  break;
              }
            } elseif ($mpesa_payment) {
              $actual_payment_method = 'M-Pesa';
              switch ($mpesa_payment['status']) {
                case 'pending':
                  $payment_status = 'pending';
                  $status_color = 'text-yellow-600';
                  $status_bg = 'bg-yellow-100';
                  break;
                case 'completed':
                  $payment_status = 'confirmed';
                  $status_color = 'text-green-600';
                  $status_bg = 'bg-green-100';
                  break;
                case 'failed':
                  $payment_status = 'cancelled';
                  $status_color = 'text-red-600';
                  $status_bg = 'bg-red-100';
                  break;
              }
            } else {
              if ($r['payment_method'] === '0' || $r['payment_method'] === 'pending') {
                $actual_payment_method = 'Pending';
                $payment_status = 'pending';
                $status_color = 'text-yellow-600';
                $status_bg = 'bg-yellow-100';
              } else {
                $actual_payment_method = ucfirst($r['payment_method']);
                $payment_status = 'completed';
                $status_color = 'text-green-600';
                $status_bg = 'bg-green-100';
              }
            }
            ?>
            <div class="flex justify-between"><span class="font-medium">Payment Method:</span> <span><?php echo htmlspecialchars($actual_payment_method); ?></span></div>
            <div class="flex justify-between"><span class="font-medium">Payment Status:</span> <span>
              <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_bg . ' ' . $status_color; ?>">
                <?php echo ucfirst($payment_status); ?>
              </span>
            </span></div>
            <div class="flex justify-between text-lg font-semibold"><span>Total:</span> <span>Ksh<?php echo number_format($r['fee'], 2); ?></span></div>
          </div>
          <div class="flex flex-col items-center mt-6">
            <div class="text-xs text-gray-400 mb-1">Scan QR for Access</div>
            <?php if (!empty($r['booking_ref']) && $payment_status === 'confirmed'): ?>
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($r['booking_ref']); ?>" alt="QR Code" class="w-24 h-24 bg-gray-100 rounded shadow">
            <?php else: ?>
              <i class="ri-qr-code-line text-4xl text-gray-400"></i>
            <?php endif; ?>
          </div>
          <div class="flex justify-end mt-4">
            <button onclick="printCard(this)" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 transition flex items-center"><i class="ri-printer-line mr-2"></i>Print</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
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
function printCard(btn) {
  var card = btn.closest('.fade-in');
  var printContents = card.innerHTML;
  var win = window.open('', '', 'height=600,width=400');
  win.document.write('<html><head><title>Print Receipt</title>');
  win.document.write('<link rel="stylesheet" href="https://cdn.tailwindcss.com">');
  win.document.write('</head><body>');
  win.document.write(printContents);
  win.document.write('</body></html>');
  win.document.close();
  win.focus();
  setTimeout(function(){ win.print(); win.close(); }, 500);
}
// Animate cards on load
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.fade-in').forEach((el, i) => {
    el.style.animationDelay = (i * 0.1) + 's';
  });
});
</script>
</body>
</html>
