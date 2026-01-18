<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../components/function.php';
require_once '../components/function_mailer.php';

// Validate required fields
if (empty($_POST['id']) || empty($_POST['status'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$payment_id = (int)$_POST['id'];
$new_status = trim($_POST['status']);

// Validate status
$allowed_statuses = ['pending', 'completed', 'failed', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit();
}

// Update payment status
$conn = db_connect();
$stmt = $conn->prepare("UPDATE mpesa_payments SET status = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param('si', $new_status, $payment_id);

$success = $stmt->execute();

// If payment is completed, send confirmation email
if ($success && $new_status === 'completed') {
    // Get payment details and user information
    $stmt = $conn->prepare("
        SELECT mp.*, r.*, u.email, u.name, ps.name as spot_name 
        FROM mpesa_payments mp 
        JOIN reservations r ON mp.reservation_id = r.id 
        JOIN users u ON mp.user_id = u.id 
        JOIN parking_spots ps ON r.spot_id = ps.id 
        WHERE mp.id = ?
    ");
    $stmt->bind_param('i', $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment_data = $result->fetch_assoc();
    $stmt->close();
    
    if ($payment_data) {
        $booking_data = [
            'booking_ref' => $payment_data['booking_ref'],
            'spot_id' => $payment_data['spot_id'],
            'spot_number' => $payment_data['spot_number'],
            'spot_name' => $payment_data['spot_name'],
            'license_plate' => $payment_data['license_plate'],
            'vehicle_type' => $payment_data['vehicle_type'],
            'vehicle_model' => $payment_data['vehicle_model'],
            'entry_time' => $payment_data['entry_time'],
            'exit_time' => $payment_data['exit_time'],
            'payment_method' => 'mpesa',
            'fee' => $payment_data['fee']
        ];
        
        send_booking_confirmation_email($payment_data['email'], $payment_data['name'], $booking_data);
    }
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?> 