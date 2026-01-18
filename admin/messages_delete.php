<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing user_id']);
    exit();
}

require_once '../components/function.php';
$conn = db_connect();

$admin_id = $_SESSION['user_id'];
$user_id = intval($_POST['user_id']);

// Delete all messages between admin and the selected user
$stmt = $conn->prepare("DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
$stmt->bind_param('iiii', $admin_id, $user_id, $user_id, $admin_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete messages']);
}

$stmt->close();
$conn->close(); 