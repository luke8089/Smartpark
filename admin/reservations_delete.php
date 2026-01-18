<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid reservation ID']);
    exit();
}

require_once '../components/function.php';

try {
    $conn = db_connect();
    $reservation_id = (int)$_POST['id'];
    
    // Check if reservation exists
    $check_stmt = $conn->prepare("SELECT id, user_id, booking_ref, payment_method FROM reservations WHERE id = ?");
    $check_stmt->bind_param('i', $reservation_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $reservation = $result->fetch_assoc();
    $check_stmt->close();
    
    if (!$reservation) {
        echo json_encode(['success' => false, 'error' => 'Reservation not found']);
        exit();
    }
    
    // Start transaction for data integrity
    $conn->begin_transaction();
    
    try {
        // Delete related equity payments first (if any)
        $delete_equity_stmt = $conn->prepare("DELETE FROM equity_payments WHERE reservation_id = ?");
        $delete_equity_stmt->bind_param('i', $reservation_id);
        $delete_equity_stmt->execute();
        $delete_equity_stmt->close();
        
        // Delete related mpesa payments first (if any)
        $delete_mpesa_stmt = $conn->prepare("DELETE FROM mpesa_payments WHERE reservation_id = ?");
        $delete_mpesa_stmt->bind_param('i', $reservation_id);
        $delete_mpesa_stmt->execute();
        $delete_mpesa_stmt->close();
        
        // Delete the reservation
        $delete_reservation_stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
        $delete_reservation_stmt->bind_param('i', $reservation_id);
        $success = $delete_reservation_stmt->execute();
        $delete_reservation_stmt->close();
        
        if ($success) {
            // Commit the transaction
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Reservation deleted successfully']);
        } else {
            // Rollback on failure
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Failed to delete reservation']);
        }
        
    } catch (Exception $e) {
        // Rollback on any error
        $conn->rollback();
        throw $e;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 