<?php
session_start();
require_once '../../components/function.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Get POST data
$phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
$amount = round(floatval($_POST['amount'] ?? 0));
$reservation_id = intval($_POST['reservation_id'] ?? 0);
$booking_ref = $_POST['booking_ref'] ?? '';
$user_id = $_SESSION['user']['id'] ?? null;

// Format phone to 254xxxxxxxxx
if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
    $phone = '254' . substr($phone, 1);
}
if (strlen($phone) !== 12 || substr($phone, 0, 3) !== '254') {
    die('<div style="color:red;text-align:center;margin-top:2em;">Invalid phone number format. <a href="index.php">Back</a></div>');
}

// Insert payment attempt in mpesa_payments
$conn = db_connect();
$stmt = $conn->prepare('INSERT INTO mpesa_payments (user_id, reservation_id, phone, amount, status, created_at) VALUES (?, ?, ?, ?, "pending", NOW())');
$stmt->bind_param('iisd', $user_id, $reservation_id, $phone, $amount);
$stmt->execute();
$payment_id = $conn->insert_id;
$stmt->close();

// Prepare STK Push
$Timestamp = date('YmdHis');
$Password = base64_encode(MPESA_BUSINESS_SHORT_CODE . MPESA_PASSKEY . $Timestamp);

// Get access token
$ch = curl_init(MPESA_ACCESS_TOKEN_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_USERPWD, MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
$result = curl_exec($ch);
curl_close($ch);
$result = json_decode($result);
if (!isset($result->access_token)) {
    $conn->query("UPDATE mpesa_payments SET status='failed' WHERE id=$payment_id");
    die('<div style="color:red;text-align:center;margin-top:2em;">Failed to get M-Pesa access token. <a href="../index.php">Back</a></div>');
}
$accessToken = $result->access_token;

// Initiate STK Push
$stkHeader = ['Content-Type:application/json', 'Authorization:Bearer ' . $accessToken];
$stkPayload = [
    'BusinessShortCode' => MPESA_BUSINESS_SHORT_CODE,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => MPESA_BUSINESS_SHORT_CODE,
    'PhoneNumber' => $phone,
    'CallBackURL' => MPESA_CALLBACK_URL,
    'AccountReference' => $booking_ref,
    'TransactionDesc' => 'SmartPark Payment'
];
$ch = curl_init(MPESA_STK_PUSH_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, $stkHeader);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPayload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$stkResponse = curl_exec($ch);
curl_close($ch);
$response = json_decode($stkResponse);

$merchantRequestId = $response->MerchantRequestID ?? '';
$checkoutRequestId = $response->CheckoutRequestID ?? '';
$responseCode = $response->ResponseCode ?? '';
$responseDesc = $response->ResponseDescription ?? '';

// Update mpesa_payments with merchant/checkout IDs
$stmt = $conn->prepare('UPDATE mpesa_payments SET mpesa_ref=?, status=?, updated_at=NOW() WHERE id=?');
$status = ($responseCode === '0') ? 'pending' : 'failed';
$mpesa_ref = $merchantRequestId;
$stmt->bind_param('ssi', $mpesa_ref, $status, $payment_id);
$stmt->execute();
$stmt->close();
$conn->close();

if ($responseCode !== '0') {
    echo '<div style="color:red;text-align:center;margin-top:2em;">STK push initiation failed: ' . htmlspecialchars($responseDesc) . '<br><a href="index.php">Back</a></div>';
    exit();
}
// Show modern waiting for payment page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Waiting for M-PESA Payment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #e0e7ef 0%, #f5f7fa 100%);
      font-family: 'Montserrat', sans-serif;
      overflow-x: hidden;
      position: relative;
    }
    .animated-bg {
      position: fixed;
      top: 0; left: 0; width: 100vw; height: 100vh;
      z-index: 0;
      pointer-events: none;
      overflow: hidden;
    }
    .circle {
      position: absolute;
      border-radius: 50%;
      opacity: 0.12;
      animation: float 12s infinite linear;
    }
    .circle1 { width: 180px; height: 180px; background: #00A86B; left: 10vw; top: 10vh; animation-delay: 0s; }
    .circle2 { width: 120px; height: 120px; background: #20215B; right: 8vw; top: 30vh; animation-delay: 2s; }
    .circle3 { width: 90px; height: 90px; background: #00A86B; left: 20vw; bottom: 10vh; animation-delay: 4s; }
    .circle4 { width: 60px; height: 60px; background: #20215B; right: 18vw; bottom: 18vh; animation-delay: 6s; }
    @keyframes float {
      0% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-30px) scale(1.08); }
      100% { transform: translateY(0) scale(1); }
    }
    .waiting-card {
      max-width: 420px;
      border-radius: 1.5rem;
      box-shadow: 0 8px 32px 0 rgba(31,38,135,0.13);
      background: rgba(255,255,255,0.98);
      padding: 2.5rem 2rem 2rem 2rem;
      text-align: center;
      margin: 0 auto;
      position: relative;
      z-index: 1;
      animation: fadeInUp 0.8s cubic-bezier(.39,.575,.565,1) both;
    }
    @keyframes fadeInUp {
      0% { opacity: 0; transform: translateY(40px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    .spinner-border {
      width: 3.5rem; height: 3.5rem; margin-bottom: 1.5rem; color: #00A86B;
      animation: spinPulse 1.2s linear infinite;
    }
    @keyframes spinPulse {
      0% { filter: brightness(1); }
      50% { filter: brightness(1.3); }
      100% { filter: brightness(1); }
    }
    .progress {
      height: 7px;
      border-radius: 5px;
      background: #e6f9f0;
      margin-bottom: 1.5rem;
      overflow: hidden;
    }
    .progress-bar {
      background: linear-gradient(90deg, #00A86B 0%, #20215B 100%);
      animation: progressAnim 2.5s linear infinite;
    }
    @keyframes progressAnim {
      0% { width: 0%; }
      50% { width: 90%; }
      100% { width: 0%; }
    }
    .mpesa-phone {
      font-size: 1.1rem;
      color: #00A86B;
      font-weight: 600;
      letter-spacing: 1px;
    }
    .waiting-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #20215B;
    }
    .waiting-desc {
      color: #444;
      font-size: 1.08rem;
      margin-bottom: 0.7rem;
    }
    .waiting-muted {
      color: #888;
      font-size: 0.98rem;
      margin-bottom: 1.2rem;
    }
    .btn-success {
      background: #00A86B;
      border: none;
      font-weight: 600;
      border-radius: 0.7rem;
      font-size: 1.1rem;
      padding: 0.7rem 0;
      transition: background 0.2s, box-shadow 0.2s;
      box-shadow: 0 2px 8px rgba(0,168,107,0.08);
    }
    .btn-success:hover, .btn-success:focus {
      background: #008F5B;
      box-shadow: 0 4px 16px rgba(0,168,107,0.18);
    }
    .btn-link { color: #20215B; font-weight: 500; }
    .btn-link:hover { color: #00A86B; }
    .animated-cancel {
      position: relative;
      display: inline-block;
      color: #e3342f; /* red-600 */
      font-weight: 500;
      text-decoration: none;
      overflow: hidden;
      transition: color 0.25s cubic-bezier(.4,0,.2,1);
    }
    .animated-cancel::after {
      content: '';
      position: absolute;
      left: 0; bottom: 0;
      width: 100%; height: 2px;
      background: linear-gradient(90deg, #e3342f 0%, #b91c1c 100%);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.35s cubic-bezier(.4,0,.2,1);
    }
    .animated-cancel:hover {
      color: #b91c1c; /* red-700 */
      transform: translateX(6px);
    }
    .animated-cancel:hover::after {
      transform: scaleX(1);
    }
  </style>
</head>
<body>
  <div class="animated-bg">
    <div class="circle circle1"></div>
    <div class="circle circle2"></div>
    <div class="circle circle3"></div>
    <div class="circle circle4"></div>
  </div>
  <div class="waiting-card mt-5">
    <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
    <div class="progress mb-3">
      <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 80%"></div>
    </div>
    <div class="waiting-title mb-2">Waiting for Payment</div>
    <div class="waiting-desc mb-2">A payment prompt has been sent to your phone:<br><span class="mpesa-phone"><?php echo htmlspecialchars($phone); ?></span></div>
    <div class="waiting-muted">Complete the payment on your phone to continue.<br>Do not close this page.</div>
    <?php
    $spot_id = isset($_POST['spot_id']) ? intval($_POST['spot_id']) : (isset($_SESSION['spot_id']) ? intval($_SESSION['spot_id']) : 0);
    if ($spot_id) {
        $_SESSION['spot_id'] = $spot_id;
    }
    ?>
    <form method="get" action="../receipt.php">
      <input type="hidden" name="booking_ref" value="<?php echo htmlspecialchars($booking_ref); ?>">
      <?php if ($spot_id): ?>
        <input type="hidden" name="spot_id" value="<?php echo htmlspecialchars($spot_id); ?>">
      <?php endif; ?>
     
    </form>
    <a href="../parking.php" class="btn btn-link mt-3 animated-cancel">Cancel</a>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // AJAX polling for payment status
    setInterval(function() {
      $.get('check_mpesa_status.php', { booking_ref: '<?php echo addslashes($booking_ref); ?>' }, function(data) {
        if (data.status === 'completed') {
          // Redirect to receipt with booking_ref and spot_id if present in response
          var url = '../receipt.php?booking_ref=<?php echo urlencode($booking_ref); ?>';
          if (data.spot_id) {
            url += '&spot_id=' + encodeURIComponent(data.spot_id);
          }
          window.location.href = url;
        }
      }, 'json');
    }, 4000);
  </script>
</body>
</html>
