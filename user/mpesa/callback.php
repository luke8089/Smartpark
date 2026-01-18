<?php
require_once '../config/database.php';

// Get the callback data
$callbackJSONData = file_get_contents('php://input');

// For debugging purposes, write the raw callback to a file
$logFile = "callback_log.txt";
$log = "[" . date("Y-m-d H:i:s") . "] " . $callbackJSONData . PHP_EOL;
file_put_contents($logFile, $log, FILE_APPEND);

$callbackData = json_decode($callbackJSONData);

$resultCode = $callbackData->Body->stkCallback->ResultCode;
$resultDesc = $callbackData->Body->stkCallback->ResultDesc;
$merchantRequestID = $callbackData->Body->stkCallback->MerchantRequestID;
$checkoutRequestID = $callbackData->Body->stkCallback->CheckoutRequestID;

$pdo = getDBConnection();

if ($resultCode === 0) {
    // Payment was successful
    $amount = $callbackData->Body->stkCallback->CallbackMetadata->Item[0]->Value;
    $mpesaReceiptNumber = $callbackData->Body->stkCallback->CallbackMetadata->Item[1]->Value;
    $paymentDate = $callbackData->Body->stkCallback->CallbackMetadata->Item[3]->Value;
    
    // Convert date format
    $paymentTimestamp = date('Y-m-d H:i:s', strtotime($paymentDate));

    // Update payments table
    $stmt = $pdo->prepare(
        "UPDATE payments SET status = 'completed', mpesa_receipt_number = ?, payment_date = ?, updated_at = NOW() 
         WHERE merchant_request_id = ?"
    );
    $stmt->execute([$mpesaReceiptNumber, $paymentTimestamp, $merchantRequestID]);

    // Update appointment table
    $stmt = $pdo->prepare(
        "UPDATE appointments SET payment_status = 'paid' 
         WHERE id = (SELECT appointment_id FROM payments WHERE merchant_request_id = ?)"
    );
    $stmt->execute([$merchantRequestID]);

} else {
    // Payment failed or was cancelled
    $status = ($resultDesc === 'The service request is processed successfully.') ? 'cancelled' : 'failed';

    // Update payments table
    $stmt = $pdo->prepare(
        "UPDATE payments SET status = ?, updated_at = NOW() 
         WHERE merchant_request_id = ?"
    );
    $stmt->execute([$status, $merchantRequestID]);

    // Update appointment table
    $stmt = $pdo->prepare(
        "UPDATE appointments SET payment_status = 'failed' 
         WHERE id = (SELECT appointment_id FROM payments WHERE merchant_request_id = ?)"
    );
    $stmt->execute([$merchantRequestID]);
}

// Respond to Safaricom
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
?> 