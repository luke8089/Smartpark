<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit();
}
require_once '../components/function.php';
$conn = db_connect();
$admin_id = $_SESSION['user_id'];
$user_id = (int)$_GET['user_id'];
$stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
$stmt->bind_param('iiii', $admin_id, $user_id, $user_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode(['success' => true, 'messages' => $messages]); 