<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid payment ID']);
    exit();
}

require_once '../components/function.php';

try {
    $conn = db_connect();
    $payment_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("
        SELECT e.*, u.name as user_name, r.booking_ref, r.license_plate, r.vehicle_type, r.vehicle_model, r.entry_time, r.exit_time
        FROM equity_payments e 
        JOIN users u ON e.user_id = u.id 
        LEFT JOIN reservations r ON e.reservation_id = r.id 
        WHERE e.id = ?
    ");
    $stmt->bind_param('i', $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    if ($payment) {
        echo json_encode(['success' => true, 'payment' => $payment]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 