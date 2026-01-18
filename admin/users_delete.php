<?php
require_once '../components/function.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? null;
  if ($id) {
    $conn = db_connect();
    $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => $success]);
    exit;
  }
}
echo json_encode(['success' => false, 'error' => 'Invalid request']); 