<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Message ID required']);
    exit();
}

require_once '../components/function.php';
$conn = db_connect();

$message_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->bind_param('i', $message_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Message not found']);
    exit();
}

$message = $result->fetch_assoc();

// Mark as read if it's new
if ($message['status'] === 'new') {
    $update_stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $update_stmt->bind_param('i', $message_id);
    $update_stmt->execute();
    $update_stmt->close();
    $message['status'] = 'read';
}

echo json_encode(['success' => true, 'message' => $message]);

$stmt->close();
$conn->close();
?> 