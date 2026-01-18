<?php
header("Content-Type: application/json");

include '../../connect.php';
include '../../mailer_config.php';

$response = '{
    "ResultCode": 0, 
    "ResultDesc": "Confirmation Received Successfully"
}';

$mpesaResponse = file_get_contents('php://input');

// log the response
$logFile = "M_PESAConfirmationResponse.txt";
$log = fopen($logFile, "a");
fwrite($log, $mpesaResponse);
fclose($log);

// Parse callback
$data = json_decode($mpesaResponse);
if (isset($data->Body->stkCallback)) {
    $callback = $data->Body->stkCallback;
    $resultCode = $callback->ResultCode;
    $merchantRequestID = $callback->MerchantRequestID ?? '';
    $mpesaReceipt = '';
    $amount = 0;
    $paymentDate = '';
    $phone = '';
    if (isset($callback->CallbackMetadata)) {
        foreach ($callback->CallbackMetadata->Item as $item) {
            if ($item->Name === 'MpesaReceiptNumber') $mpesaReceipt = $item->Value;
            if ($item->Name === 'Amount') $amount = $item->Value;
            if ($item->Name === 'TransactionDate') $paymentDate = $item->Value;
            if ($item->Name === 'PhoneNumber') $phone = $item->Value;
        }
        // Try to find payment by mpesa_receipt
        $stmt = $conn->prepare("SELECT * FROM rent_payments WHERE mpesa_receipt = ? LIMIT 1");
        $stmt->bind_param('s', $mpesaReceipt);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        if (!$row && $merchantRequestID) {
            // Try to find payment by merchantRequestID (if stored in mpesa_receipt)
            $stmt = $conn->prepare("SELECT * FROM rent_payments WHERE mpesa_receipt = ? LIMIT 1");
            $stmt->bind_param('s', $merchantRequestID);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if ($row) {
                file_put_contents('email_debug_log.txt', date('c')." Matched payment by merchantRequestID $merchantRequestID\n", FILE_APPEND);
            }
        }
        if (!$row) {
            // Fallback: try to find by phone, amount, and status='pending'
            $stmt = $conn->prepare("SELECT * FROM rent_payments WHERE amount = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param('d', $amount);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if ($row) {
                file_put_contents('email_debug_log.txt', date('c')." Fallback matched payment by amount $amount and status pending\n", FILE_APPEND);
            }
        }
        if ($row) {
            $paymentId = $row['id'];
            $tenantId = $row['tenant_id'];
            $leaseId = $row['lease_id'];
            $paidAmount = floatval($row['amount']);
            // Update payment status if successful
            if ($mpesaReceipt) {
                $update = $conn->prepare("UPDATE rent_payments SET mpesa_receipt=?, updated_at=NOW() WHERE id=?");
                $update->bind_param('si', $mpesaReceipt, $paymentId);
                $update->execute();
                $update->close();
            }
            // Fetch tenant email and name using tenantId from rent_payments
            $userQ = $conn->prepare("SELECT fullName, email FROM users WHERE userId=?");
            $userQ->bind_param('i', $tenantId);
            $userQ->execute();
            $userRes = $userQ->get_result();
            if ($user = $userRes->fetch_assoc()) {
                $credit = $amount - $paidAmount;
                if ($credit < 0) $credit = 0;
                if ($mpesaReceipt && $user['email']) {
                    file_put_contents('email_debug_log.txt', date('c')." Sending receipt to {$user['email']} for paymentId $paymentId (status: {$row['status']})\n", FILE_APPEND);
                    sendPaymentReceiptEmail($user['email'], $user['fullName'], $amount, $mpesaReceipt, $credit);
                }
            } else {
                file_put_contents('email_debug_log.txt', date('c')." Tenant not found for tenantId $tenantId (paymentId $paymentId)\n", FILE_APPEND);
            }
        } else {
            file_put_contents('email_debug_log.txt', date('c')." Payment not found for mpesa_receipt $mpesaReceipt, merchantRequestID $merchantRequestID, or fallback\n", FILE_APPEND);
        }
    }
}
echo $response;
 
     
