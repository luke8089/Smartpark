<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'error' => 'Message ID and status required']);
    exit();
}

$valid_statuses = ['new', 'read', 'replied', 'closed'];
if (!in_array($input['status'], $valid_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit();
}

require_once '../components/function.php';
$conn = db_connect();

$message_id = intval($input['message_id']);
$status = $input['status'];

$stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $message_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?> 