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

require_once '../components/function.php';

try {
    $conn = db_connect();
    $payment_id = (int)$_POST['id'];
    
    // Check if payment exists
    $check_stmt = $conn->prepare("SELECT id, user_id, reservation_id, amount, status FROM equity_payments WHERE id = ?");
    $check_stmt->bind_param('i', $payment_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $payment = $result->fetch_assoc();
    $check_stmt->close();
    
    if (!$payment) {
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        exit();
    }
    
    // Check if payment is completed (prevent deletion of confirmed payments)
    if ($payment['status'] === 'completed') {
        echo json_encode(['success' => false, 'error' => 'Cannot delete completed payments']);
        exit();
    }
    
    // Delete the equity payment
    $delete_stmt = $conn->prepare("DELETE FROM equity_payments WHERE id = ?");
    $delete_stmt->bind_param('i', $payment_id);
    $success = $delete_stmt->execute();
    $delete_stmt->close();
    
    if ($success) {
        // If there's a reservation associated, update its payment method to '0' (pending)
        if ($payment['reservation_id']) {
            $update_stmt = $conn->prepare("UPDATE reservations SET payment_method = '0' WHERE id = ?");
            $update_stmt->bind_param('i', $payment['reservation_id']);
            $update_stmt->execute();
            $update_stmt->close();
        }
        
        echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete payment']);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 