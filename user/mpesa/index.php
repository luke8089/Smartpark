<?php
session_start();
require_once '../../components/function.php';

// Check if reservation/payment info is in session
if (!isset($_SESSION['mpesa_reservation_id'], $_SESSION['mpesa_amount'], $_SESSION['mpesa_booking_ref'])) {
    // No reservation info, redirect back to booking
    header('Location: ../receipt.php');
    exit();
}
$reservation_id = $_SESSION['mpesa_reservation_id'];
$amount = $_SESSION['mpesa_amount'];
$booking_ref = $_SESSION['mpesa_booking_ref'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>M-PESA Payment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      font-family: 'Montserrat', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .mpesa-card {
      border-radius: 1.5rem;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
      background: rgba(255,255,255,0.95);
      overflow: hidden;
      animation: fadeInUp 0.8s cubic-bezier(.39,.575,.565,1) both;
      max-width: 400px;
      width: 100%;
    }
    @keyframes fadeInUp {
      0% { opacity: 0; transform: translateY(40px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    .mpesa-header {
      background: #00A86B;
      color: #fff;
      padding: 2rem 1.5rem 1.5rem 1.5rem;
      text-align: center;
      position: relative;
    }
    .mpesa-logo {
      width: 70px;
      height: 70px;
      object-fit: contain;
      margin-bottom: 0.5rem;
      filter: drop-shadow(0 2px 8px rgba(0,0,0,0.08));
      animation: logoPulse 2s infinite;
    }
    @keyframes logoPulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.08); }
    }
    .mpesa-title {
      font-size: 1.5rem;
      font-weight: 600;
      letter-spacing: 1px;
      margin-bottom: 0;
    }
    .mpesa-amount {
      background: #e6f9f0;
      color: #00A86B;
      font-size: 2rem;
      font-weight: 600;
      border-radius: 1rem;
      padding: 1rem 0;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,168,107,0.07);
      text-align: center;
      animation: amountPulse 2s infinite;
    }
    @keyframes amountPulse {
      0%, 100% { box-shadow: 0 2px 8px rgba(0,168,107,0.07); }
      50% { box-shadow: 0 4px 16px rgba(0,168,107,0.18); }
    }
    .form-label {
      font-weight: 500;
      color: #222;
    }
    .btn-pay {
      background: #00A86B;
      color: #fff;
      font-weight: 600;
      border-radius: 0.75rem;
      padding: 0.75rem 0;
      font-size: 1.1rem;
      transition: background 0.2s, box-shadow 0.2s;
      box-shadow: 0 2px 8px rgba(0,168,107,0.08);
    }
    .btn-pay:hover, .btn-pay:focus {
      background: #008F5B;
      box-shadow: 0 4px 16px rgba(0,168,107,0.18);
    }
    .mpesa-footer {
      text-align: center;
      font-size: 0.95rem;
      color: #888;
      padding: 1rem 0 0.5rem 0;
    }
    @media (max-width: 500px) {
      .mpesa-card { border-radius: 0.7rem; }
      .mpesa-header { padding: 1.2rem 0.5rem 1rem 0.5rem; }
      .mpesa-amount { font-size: 1.3rem; padding: 0.7rem 0; }
    }
  </style>
</head>
<body>
  <div class="mpesa-card mx-auto mt-5">
    <div class="mpesa-header">
      <img src="assets/images/mpesa.png" alt="M-PESA Logo" class="mpesa-logo">
      <div class="mpesa-title">M-PESA Payment</div>
    </div>
    <div class="card-body p-4">
      <div class="mpesa-amount mb-4">Ksh <?php echo number_format($amount,2); ?></div>
      <form method="post" action="stk_initiate.php" autocomplete="off">
        <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
        <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation_id); ?>">
        <input type="hidden" name="booking_ref" value="<?php echo htmlspecialchars($booking_ref); ?>">
        <div class="mb-3">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" class="form-control form-control-lg" placeholder="07XXXXXXXX" required pattern="07[0-9]{8}" maxlength="10" style="font-size:1.1rem;">
        </div>
        <button type="submit" class="btn btn-pay w-100 mt-2">Pay Now</button>
      </form>
    </div>
    <div class="mpesa-footer">Secured by SmartPark &bull; Powered by M-PESA</div>
  </div>
</body>
</html>