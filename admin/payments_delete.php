<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../components/function.php';

// Check if payment ID is provided
if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Payment ID is required']);
    exit();
}

$payment_id = (int)$_POST['id'];

// Validate payment ID
if ($payment_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid payment ID']);
    exit();
}

try {
    $conn = db_connect();
    
    // First, check if the payment exists
    $check_stmt = $conn->prepare("SELECT id, user_id, amount, status FROM mpesa_payments WHERE id = ?");
    $check_stmt->bind_param('i', $payment_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $payment = $result->fetch_assoc();
    $check_stmt->close();
    
    if (!$payment) {
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        exit();
    }
    
    // Delete the payment
    $delete_stmt = $conn->prepare("DELETE FROM mpesa_payments WHERE id = ?");
    $delete_stmt->bind_param('i', $payment_id);
    $success = $delete_stmt->execute();
    $delete_stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete payment']);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 