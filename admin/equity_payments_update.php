<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid payment ID']);
    exit();
}

if (!isset($_POST['status']) || !in_array($_POST['status'], ['pending', 'completed', 'failed', 'cancelled'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit();
}

require_once '../components/function.php';
require_once '../components/function_mailer.php';

try {
    $conn = db_connect();
    $payment_id = (int)$_POST['id'];
    $status = $_POST['status'];
    
    // Check if payment exists
    $check_stmt = $conn->prepare("SELECT id, status, reservation_id FROM equity_payments WHERE id = ?");
    $check_stmt->bind_param('i', $payment_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $payment = $result->fetch_assoc();
    $check_stmt->close();
    
    if (!$payment) {
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        exit();
    }
    
    // Allow updating any payment status (removed the pending-only restriction)
    
    // Update payment status
    $update_stmt = $conn->prepare("UPDATE equity_payments SET status = ?, updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param('si', $status, $payment_id);
    $success = $update_stmt->execute();
    $update_stmt->close();
    
    if ($success) {
        // If payment is completed, send confirmation email
        if ($status === 'completed' && $payment['reservation_id']) {
            // Get payment details and user information
            $stmt = $conn->prepare("
                SELECT ep.*, r.*, u.email, u.name, ps.name as spot_name 
                FROM equity_payments ep 
                JOIN reservations r ON ep.reservation_id = r.id 
                JOIN users u ON ep.user_id = u.id 
                JOIN parking_spots ps ON r.spot_id = ps.id 
                WHERE ep.id = ?
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
                    'payment_method' => 'equity',
                    'fee' => $payment_data['amount']
                ];
                
                send_booking_confirmation_email($payment_data['email'], $payment_data['name'], $booking_data);
            }
            
            // Log the completion for debugging
            error_log("Equity payment {$payment_id} completed for reservation {$payment['reservation_id']}");
        }
        
        echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update payment status']);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 