<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../reglogin.php');
    exit();
}

// Check if equity payment session data exists
if (!isset($_SESSION['equity_reservation_id']) || !isset($_SESSION['equity_amount']) || !isset($_SESSION['equity_booking_ref'])) {
    header('Location: receipt.php');
    exit();
}

require_once '../components/function.php';

$reservation_id = $_SESSION['equity_reservation_id'];
$amount = $_SESSION['equity_amount'];
$booking_ref = $_SESSION['equity_booking_ref'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_number = trim($_POST['account_number']);
    $account_name = trim($_POST['account_name']);
    
    // Validate inputs
    if (empty($account_number) || empty($account_name)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Create equity payment record
        $conn = db_connect();
        $transaction_ref = 'EQ' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
        
        $stmt = $conn->prepare("INSERT INTO equity_payments (user_id, reservation_id, account_number, account_name, amount, status, transaction_ref) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
        $user_id = $_SESSION['user']['id'];
        $stmt->bind_param('iissds', $user_id, $reservation_id, $account_number, $account_name, $amount, $transaction_ref);
        
        if ($stmt->execute()) {
            // Clear session data
            unset($_SESSION['equity_reservation_id']);
            unset($_SESSION['equity_amount']);
            unset($_SESSION['equity_booking_ref']);
            
            // Redirect to success page
            header('Location: equity_success.php?booking_ref=' . $booking_ref);
            exit();
        } else {
            $error_message = 'Payment submission failed. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equity Bank Payment - SmartPark</title>
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
                <img src="../assets/images/Equity.jpg" alt="Equity Bank" class="w-24 h-24 mx-auto mb-4 rounded-lg">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Equity Bank Payment</h1>
                <p class="text-gray-600">Please provide your Equity bank account details</p>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-blue-800">Amount to Pay:</span>
                    <span class="text-xl font-bold text-blue-900">Ksh<?php echo number_format($amount, 2); ?></span>
                </div>
                <div class="text-sm text-blue-600 mt-1">
                    Booking Reference: <?php echo htmlspecialchars($booking_ref); ?>
                </div>
            </div>
            
            <form method="post" class="space-y-6">
                <div>
                    <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Equity Bank Account Number <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="account_number" 
                        name="account_number" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your Equity account number"
                        maxlength="20"
                    >
                    <p class="text-sm text-gray-500 mt-1">Enter your Equity Bank account number without spaces</p>
                </div>
                
                <div>
                    <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Account Holder Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="account_name" 
                        name="account_name" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter account holder name"
                        maxlength="100"
                    >
                    <p class="text-sm text-gray-500 mt-1">Enter the name as it appears on your Equity Bank account</p>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="ri-information-line text-yellow-600 mt-0.5 mr-2"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium mb-1">Important Information:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Your payment will be marked as pending until confirmed by admin</li>
                                <li>Please ensure your account has sufficient funds</li>
                                <li>You will receive a confirmation once payment is verified</li>
                                <li>Keep your booking reference for tracking</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-4">
                    <a href="receipt.php" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-center">
                        <i class="ri-arrow-left-line mr-2"></i>Back
                    </a>
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="ri-bank-card-line mr-2"></i>Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 