<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../reglogin.php');
    exit();
}

if (!isset($_GET['booking_ref'])) {
    header('Location: receipt.php');
    exit();
}

require_once '../components/function.php';

$booking_ref = $_GET['booking_ref'];
$reservation = get_reservation_by_ref($booking_ref);

if (!$reservation) {
    header('Location: receipt.php');
    exit();
}

// Get equity payment details
$conn = db_connect();
$stmt = $conn->prepare("SELECT * FROM equity_payments WHERE reservation_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param('i', $reservation['id']);
$stmt->execute();
$result = $stmt->get_result();
$equity_payment = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Submitted - SmartPark</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .fade-in { opacity: 0; transform: translateY(30px) scale(0.98); animation: fadeInUp 0.7s cubic-bezier(.4,2,.6,1) forwards; }
        @keyframes fadeInUp { to { opacity: 1; transform: none; } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>
    
    <main class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-8 fade-in">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-time-line text-2xl text-yellow-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Submitted Successfully!</h1>
                <p class="text-gray-600">Your Equity Bank payment is pending confirmation</p>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="ri-information-line text-yellow-600 mt-0.5 mr-2"></i>
                    <div class="text-sm text-yellow-800">
                        <p class="font-medium mb-1">Payment Status: Pending</p>
                        <p>Your payment has been submitted and is awaiting admin confirmation. You will receive a notification once your payment is verified.</p>
                        <p class="mt-2 font-medium">Receipt Access:</p>
                        <p>Your receipt will be available for viewing and printing once the payment is confirmed by admin.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-gray-800 mb-4">Booking Details</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Booking Reference:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($reservation['booking_ref']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Amount:</span>
                        <span class="font-medium">Ksh<?php echo number_format($reservation['fee'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="font-medium">Equity Bank</span>
                    </div>
                    <?php if ($equity_payment): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Transaction Ref:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($equity_payment['transaction_ref']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Account Number:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($equity_payment['account_number']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Account Name:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($equity_payment['account_name']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="font-medium text-blue-800 mb-2">What happens next?</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Admin will verify your payment details</li>
                    <li>• You'll receive confirmation once verified</li>
                    <li>• Your parking spot will be confirmed</li>
                    <li>• You can track your booking status</li>
                </ul>
            </div>
            
            <div class="flex space-x-4">
                <a href="dashboard.php" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center">
                    <i class="ri-dashboard-line mr-2"></i>Go to Dashboard
                </a>
                <a href="receiptproduced.php" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-center">
                    <i class="ri-file-list-3-line mr-2"></i>View All Receipts
                </a>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 