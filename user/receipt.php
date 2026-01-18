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
require_once '../components/function_mailer.php';

$spots = get_parking_spots();
$selected_spot_id = isset($_POST['spot_id']) ? (int)$_POST['spot_id'] : (isset($_GET['spot_id']) ? (int)$_GET['spot_id'] : null);
$selected_spot = null;
foreach ($spots as $s) { if ($s['id'] == $selected_spot_id) { $selected_spot = $s; break; } }
$selected_spot_number = isset($_POST['spot_number']) ? (int)$_POST['spot_number'] : null;

$show_receipt = false;
$receipt = [];
$spot_map = [];
foreach ($spots as $spot) {
    $spot_map[$spot['id']] = $spot;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['spot_id'], $_POST['spot_number'], $_POST['license_plate'], $_POST['vehicle_type'], $_POST['vehicle_model'], $_POST['entry_time'], $_POST['exit_time'], $_POST['payment_method'])
) {
    // Debug: Log the submitted payment method
    error_log("Submitted payment method: " . $_POST['payment_method']);
    
    $user_id = $_SESSION['user']['id'] ?? null;
    $spot_id = (int)$_POST['spot_id'];
    $spot_number = (int)$_POST['spot_number'];
    $license_plate = trim($_POST['license_plate']);
    $vehicle_type = trim($_POST['vehicle_type']);
    $vehicle_model = trim($_POST['vehicle_model']);
    $entry_time = $_POST['entry_time'];
    $exit_time = $_POST['exit_time'];
    $payment_method = trim($_POST['payment_method']);
    // Ensure only 'mpesa' or 'equity' are stored
    if ($payment_method !== 'mpesa' && $payment_method !== 'equity') {
        $payment_method = 'mpesa';
    }
    // Debug: Log the final payment method value
    error_log("Final payment method for reservation: " . $payment_method);
    // No need to map 'paypal' to 'equity' anymore
    $booking_ref = 'BP' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 9));
    $fee = 0;
    $entry = strtotime($entry_time);
    $exit = strtotime($exit_time);
    $hours = max(1, ceil(($exit - $entry) / 3600));
    $spot = null;
    foreach ($spots as $s) { if ($s['id'] == $spot_id) { $spot = $s; break; } }
    if ($spot) {
        $fee = $hours * $spot['price_hourly'];
        if ($payment_method === 'mpesa') {
            // Only create reservation and redirect to mpesa
            if ($user_id && create_reservation($user_id, $spot_id, $spot_number, $license_plate, $vehicle_type, $vehicle_model, $entry_time, $exit_time, $fee, $payment_method, $booking_ref)) {
                $conn = db_connect();
                $stmt = $conn->prepare('SELECT id FROM reservations WHERE booking_ref = ?');
                $stmt->bind_param('s', $booking_ref);
                $stmt->execute();
                $result = $stmt->get_result();
                $reservation = $result->fetch_assoc();
                $stmt->close();
                $conn->close();
                $reservation_id = $reservation['id'] ?? null;
                $_SESSION['mpesa_reservation_id'] = $reservation_id;
                $_SESSION['mpesa_amount'] = $fee;
                $_SESSION['mpesa_booking_ref'] = $booking_ref;
                header('Location: mpesa/index.php');
                exit();
            }
        } elseif ($payment_method === 'equity') {
            // Create reservation and redirect to equity payment form
            if ($user_id && create_reservation($user_id, $spot_id, $spot_number, $license_plate, $vehicle_type, $vehicle_model, $entry_time, $exit_time, $fee, $payment_method, $booking_ref)) {
                $conn = db_connect();
                $stmt = $conn->prepare('SELECT id FROM reservations WHERE booking_ref = ?');
                $stmt->bind_param('s', $booking_ref);
                $stmt->execute();
                $result = $stmt->get_result();
                $reservation = $result->fetch_assoc();
                $stmt->close();
                $conn->close();
                $reservation_id = $reservation['id'] ?? null;
                $_SESSION['equity_reservation_id'] = $reservation_id;
                $_SESSION['equity_amount'] = $fee;
                $_SESSION['equity_booking_ref'] = $booking_ref;
                header('Location: equity_payment.php');
                exit();
            }
        } else {
            // For other payment methods, show receipt
            if ($user_id && create_reservation($user_id, $spot_id, $spot_number, $license_plate, $vehicle_type, $vehicle_model, $entry_time, $exit_time, $fee, $payment_method, $booking_ref)) {
                // Send booking confirmation email
                $booking_data = [
                    'booking_ref' => $booking_ref,
                    'spot_id' => $spot_id,
                    'spot_number' => $spot_number,
                    'spot_name' => $spot['name'],
                    'license_plate' => $license_plate,
                    'vehicle_type' => $vehicle_type,
                    'vehicle_model' => $vehicle_model,
                    'entry_time' => $entry_time,
                    'exit_time' => $exit_time,
                    'payment_method' => $payment_method,
                    'fee' => $fee
                ];
                
                // Get user email and name
                $conn = db_connect();
                $stmt = $conn->prepare('SELECT email, name FROM users WHERE id = ?');
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
                $conn->close();
                
                if ($user) {
                    send_booking_confirmation_email($user['email'], $user['name'], $booking_data);
                }
                
                $show_receipt = true;
                $receipt = $booking_data;
            } else {
                $error_message = '<div class="text-red-500 mb-4">Booking failed. Please try again.</div>';
            }
        }
    }
}
// Show receipt if redirected back with booking_ref
if (isset($_GET['booking_ref'])) {
    $reservation = get_reservation_by_ref($_GET['booking_ref']);
    if ($reservation) {
        // Check payment status for equity payments
        $can_show_receipt = true;
        if ($reservation['payment_method'] === 'equity') {
            $conn = db_connect();
            $stmt = $conn->prepare("SELECT status FROM equity_payments WHERE reservation_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param('i', $reservation['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $equity_payment = $result->fetch_assoc();
            $stmt->close();
            $conn->close();
            
            // Only show receipt if equity payment is completed
            if (!$equity_payment || $equity_payment['status'] !== 'completed') {
                $can_show_receipt = false;
                $error_message = '<div class="text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="ri-time-line text-yellow-600 mt-0.5 mr-2"></i>
                        <div>
                            <p class="font-medium text-yellow-800">Payment Pending Confirmation</p>
                            <p class="text-sm text-yellow-700 mt-1">Your Equity Bank payment is still pending admin confirmation. You will be able to view and print your receipt once the payment is verified.</p>
                        </div>
                    </div>
                </div>';
            }
        }
        
        if ($can_show_receipt) {
            // Set selected spot and number for highlighting
            $selected_spot_id =  $reservation['spot_id'];
            $selected_spot_number = $reservation['spot_number'];
            $show_receipt = true;
            $receipt = [
                'booking_ref' => $reservation['booking_ref'],
                'spot_id' => $reservation['spot_id'],
                'spot_number' => $reservation['spot_number'],
                'spot_name' => $spot_map[$reservation['spot_id']]['name'] ?? '',
                'license_plate' => $reservation['license_plate'],
                'vehicle_type' => $reservation['vehicle_type'],
                'vehicle_model' => $reservation['vehicle_model'],
                'entry_time' => $reservation['entry_time'],
                'exit_time' => $reservation['exit_time'],
                'payment_method' => $reservation['payment_method'],
                'fee' => $reservation['fee']
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SmartPark Innovations - Parking Spot Booking</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<style>
:where([class^="ri-"])::before { content: "\f3c2"; }
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
-webkit-appearance: none;
margin: 0;
}
.parking-spot {
transition: all 0.3s ease;
}
.parking-spot:hover {
transform: scale(1.05);
}
</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include 'includes/header.php'; ?>
<main class="max-w-7xl mx-auto px-4 py-8">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
<div class="bg-white rounded shadow-sm p-6">
<h2 class="text-xl font-semibold mb-6">Select Parking Spot</h2>
<div class="grid grid-cols-5 gap-4 mb-6">
<div class="col-span-5 grid grid-cols-5 gap-4">
<?php
if ($selected_spot) {
    $total = (int)$selected_spot['available_spots'];
    $reserved = get_reserved_spot_numbers($selected_spot_id);
    for ($i = 1; $i <= $total; $i++) {
        $is_reserved = in_array($i, $reserved);
        $is_selected = $selected_spot_number === $i;
        echo '<form method="post" style="display:inline;">';
        echo '<input type="hidden" name="spot_id" value="' . $selected_spot_id . '">';
        echo '<input type="hidden" name="spot_number" value="' . $i . '">';
        echo '<button type="submit" class="parking-spot aspect-square rounded flex items-center justify-center font-medium ' .
            ($is_reserved ? 'bg-red-500 text-white' : ($is_selected ? 'bg-blue-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white')) .
            '"' .
            ($is_reserved ? ' disabled' : '') .
            ' style="width:72px;height:72px;margin:4px;font-size:1.5rem;">';
        if ($is_selected) {
            echo '<i class="ri-check-line text-2xl"></i>';
        } else {
            echo $i;
        }
        echo '</button>';
        echo '</form>';
    }
} else {
    // No spot selected, show a message or empty grid
    echo '<div class="text-gray-400 col-span-5">Select a parking location to see spots.</div>';
}
?>
</div>
</div>
<div class="flex items-center space-x-4 mt-4">
<div class="flex items-center">
<div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
<span class="text-sm">Available</span>
</div>
<div class="flex items-center">
<div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
<span class="text-sm">Occupied</span>
</div>
<div class="flex items-center">
<div class="w-4 h-4 bg-[#20215B] rounded mr-2"></div>
<span class="text-sm">Selected</span>
</div>
</div>
</div>
<div class="bg-white rounded shadow-sm p-6">
<h2 class="text-xl font-semibold mb-6">Booking Details</h2>
<?php if ($show_receipt): ?>
<div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 border border-green-400 mt-8" id="receiptSection">
<div class="flex justify-between items-center mb-6">
<h3 class="text-2xl font-semibold">Booking Receipt</h3>
</div>
<div class="space-y-4">
<div class="flex justify-between text-sm">
<span>Booking Reference:</span>
<span class="font-semibold"><?php echo htmlspecialchars($receipt['booking_ref']); ?></span>
</div>
<div class="flex justify-between">
<span>Parking Spot:</span>
<span class="font-semibold"><?php echo htmlspecialchars($receipt['spot_name']) . ' (Spot #' . htmlspecialchars($receipt['spot_number']) . ')'; ?></span>
</div>
<div class="flex justify-between">
<span>License Plate:</span>
<span class="font-semibold"><?php echo htmlspecialchars($receipt['license_plate']); ?></span>
</div>
<div class="flex justify-between">
<span>Vehicle:</span>
<span class="font-semibold"><?php echo htmlspecialchars($receipt['vehicle_type'] . ' - ' . $receipt['vehicle_model']); ?></span>
</div>
<div class="flex justify-between">
<span>Entry Time:</span>
<span class="font-semibold"><?php echo date('Y-m-d H:i', strtotime($receipt['entry_time'])); ?></span>
</div>
<div class="flex justify-between">
<span>Exit Time:</span>
<span class="font-semibold"><?php echo date('Y-m-d H:i', strtotime($receipt['exit_time'])); ?></span>
</div>
<div class="flex justify-between">
<span>Payment Method:</span>
<span class="font-semibold"><?php
  $pm = strtolower($receipt['payment_method']);
  // Debug: Log the payment method value
  error_log("Payment method in receipt: " . $receipt['payment_method'] . " (lowercase: " . $pm . ")");
  echo ($pm === 'mpesa' || $pm === 'equity') ? ucfirst($pm) : 'Mpesa';
?></span>
</div>
<?php
// Get payment status and method - check for any related payments regardless of reservation payment_method
$payment_status = 'pending'; // Default to pending
$status_color = 'text-yellow-600';
$status_bg = 'bg-yellow-100';
$actual_payment_method = 'Pending';

$conn = db_connect();

// Check for equity payments first
$stmt = $conn->prepare("SELECT status FROM equity_payments WHERE reservation_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param('i', $reservation['id']);
$stmt->execute();
$result = $stmt->get_result();
$equity_payment = $result->fetch_assoc();
$stmt->close();

// Check for mpesa payments
$stmt = $conn->prepare("SELECT status FROM mpesa_payments WHERE reservation_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param('i', $reservation['id']);
$stmt->execute();
$result = $stmt->get_result();
$mpesa_payment = $result->fetch_assoc();
$stmt->close();

$conn->close();

// Determine payment status and method based on actual payment records
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
    // No payment records found - check if reservation payment_method indicates pending
    if ($receipt['payment_method'] === '0' || $receipt['payment_method'] === 'pending') {
        $actual_payment_method = 'Pending';
        $payment_status = 'pending';
        $status_color = 'text-yellow-600';
        $status_bg = 'bg-yellow-100';
    } else {
        // For other cases, assume completed
        $actual_payment_method = ucfirst($receipt['payment_method']);
        $payment_status = 'completed';
        $status_color = 'text-green-600';
        $status_bg = 'bg-green-100';
    }
}
?>
<div class="flex justify-between">
<span>Payment Method:</span>
<span class="font-semibold"><?php echo htmlspecialchars($actual_payment_method); ?></span>
</div>
<div class="flex justify-between">
<span>Payment Status:</span>
<span class="font-semibold">
    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_bg . ' ' . $status_color; ?>">
        <?php echo ucfirst($payment_status); ?>
    </span>
</span>
</div>
<div class="flex justify-between text-lg font-semibold">
<span>Total Amount:</span>
<span>Ksh<?php echo number_format($receipt['fee'], 2); ?></span>
</div>
</div>
<div class="border-t pt-4 text-center mt-6">
<div class="text-sm text-gray-500 mb-2">Scan QR Code for Parking Access</div>
<div class="w-32 h-32 mx-auto bg-gray-200 flex items-center justify-center">
<?php if (!empty($receipt['booking_ref']) && $payment_status === 'confirmed'): ?>
<img src="https://api.qrserver.com/v1/create-qr-code/?size=128x128&data=<?php echo urlencode($receipt['booking_ref']); ?>" alt="QR Code" class="w-28 h-28">
<?php else: ?>
<i class="ri-qr-code-line text-4xl text-gray-400"></i>
<?php endif; ?>
</div>
</div>
<div class="flex justify-end mt-6">
<button onclick="printReceipt()" class="px-6 py-2 bg-[#20215B] text-white rounded-[10px] hover:bg-[#2d2e73] transition-colors whitespace-nowrap cursor-pointer"><i class="ri-printer-line mr-2"></i>Print Receipt</button>
</div>
</div>
<script>
function printReceipt() {
  var printContents = document.getElementById('receiptSection').innerHTML;
  var originalContents = document.body.innerHTML;
  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
  location.reload();
}
</script>
<?php else: ?>
<form method="post" class="space-y-6">
<input type="hidden" name="spot_id" value="<?php echo htmlspecialchars($selected_spot_id); ?>">
<input type="hidden" name="spot_number" value="<?php echo htmlspecialchars($selected_spot_number); ?>">
<div>
<label class="block text-sm font-medium text-gray-700 mb-2">License Plate Number</label>
<input type="text" name="license_plate" required class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Enter license plate number">
</div>
<div>
<label class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type</label>
<div class="relative">
<select name="vehicle_type" required class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20 appearance-none pr-8">
<option value="">Select vehicle type</option>
<option value="sedan">Sedan</option>
<option value="suv">SUV</option>
<option value="van">Van</option>
<option value="motorcycle">Motorcycle</option>
</select>
<div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
<i class="ri-arrow-down-s-line text-gray-400"></i>
</div>
</div>
</div>
<div>
<label class="block text-sm font-medium text-gray-700 mb-2">Vehicle Model</label>
<input type="text" name="vehicle_model" required class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Enter vehicle model">
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium text-gray-700 mb-2">Entry Time</label>
<input type="datetime-local" name="entry_time" required class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20" value="<?php echo date('Y-m-d\TH:i'); ?>">
</div>
<div>
<label class="block text-sm font-medium text-gray-700 mb-2">Exit Time</label>
<input type="datetime-local" name="exit_time" required class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20" value="<?php echo date('Y-m-d\TH:i', strtotime('+2 hours')); ?>">
</div>
</div>
<div class="bg-gray-50 p-4 rounded">
<div class="flex justify-between items-center">
<span class="font-medium">Parking Fee:</span>
<span class="text-lg font-semibold">
<?php
if ($selected_spot_id) {
    $entry = strtotime(date('Y-m-d H:i'));
    $exit = strtotime(date('Y-m-d H:i', strtotime('+2 hours')));
    $hours = max(1, ceil(($exit - $entry) / 3600));
    $spot = null;
    foreach ($spots as $s) { if ($s['id'] == $selected_spot_id) { $spot = $s; break; } }
    if ($spot) {
        echo 'Ksh' . number_format($hours * $spot['price_hourly'], 2);
    } else {
        echo 'Ksh0.00';
    }
} else {
    echo 'Ksh0.00';
}
?>
</span>
</div>
</div>
<div>
<label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
<div class="grid grid-cols-2 gap-4">
<label class="payment-method flex flex-col items-center justify-center p-4 border rounded-button hover:border-green-500 cursor-pointer">
<input type="radio" name="payment_method" value="mpesa" required class="mb-2"> <img src="../assets/images/mpesa.png" alt="M-Pesa" class="h-8 mb-2">
<span class="text-sm">M-Pesa</span>
</label>
<label class="payment-method flex flex-col items-center justify-center p-4 border rounded-button hover:border-blue-500 cursor-pointer">
<input type="radio" name="payment_method" value="equity" required class="mb-2"> <img src="../assets/images/Equity.jpg" alt="Equity" class="h-8 mb-2">
<span class="text-sm">Equity</span>
</label>
</div>
</div>
<div class="flex items-start">
<input type="checkbox" name="terms" required class="mt-1">
<label class="ml-2 text-sm text-gray-600">I agree to the <a href="terms.php" class="text-primary hover:underline">Terms and Conditions</a></label>
</div>
<button type="submit" class="w-full bg-[#20215B] text-white py-3 rounded-[10px] hover:bg-[#2d2e73] transition-colors !rounded-button whitespace-nowrap cursor-pointer">
Confirm Booking
</button>
</form>
<?php endif; ?>
</div>
</div>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>