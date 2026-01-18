<?php
require_once '../components/function.php';
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $conn = db_connect();
  $stmt = $conn->prepare('SELECT m.*, u.name as user_name FROM mpesa_payments m JOIN users u ON m.user_id = u.id WHERE m.id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $payment = $result->fetch_assoc();
  $stmt->close();
  $conn->close();
  if ($payment) {
    echo json_encode(['success' => true, 'payment' => $payment]);
    exit;
  }
}
echo json_encode(['success' => false, 'error' => 'Payment not found']); 