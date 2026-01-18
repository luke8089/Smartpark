<?php
header('Content-Type: application/json');
require_once '../components/function.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $conn = db_connect();
  $stmt = $conn->prepare('SELECT id, name, email, role, profile_image, created_at FROM users WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();
  $conn->close();
  
  if ($user) {
    echo json_encode(['success' => true, 'user' => $user]);
    exit;
  }
}
echo json_encode(['success' => false, 'error' => 'User not found']);
?> 