<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}
if (!isset($_POST['receiver_id']) || !is_numeric($_POST['receiver_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid receiver ID']);
    exit();
}
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
if ($message === '') {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}
require_once '../components/function.php';
$conn = db_connect();
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
$stmt->bind_param('iiss', $_SESSION['user_id'], $_POST['receiver_id'], $subject, $message);
$success = $stmt->execute();
$stmt->close();
$conn->close();
echo json_encode(['success' => $success]); 