<?php
header('Content-Type: application/json');
require_once '../components/function.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $conn = db_connect();
  $stmt = $conn->prepare('SELECT r.*, u.name as user_name, s.name as spot_name FROM reservations r JOIN users u ON r.user_id = u.id JOIN parking_spots s ON r.spot_id = s.id WHERE r.id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $reservation = $result->fetch_assoc();
  $stmt->close();
  $conn->close();
  
  if ($reservation) {
    echo json_encode(['success' => true, 'reservation' => $reservation]);
    exit;
  }
}
echo json_encode(['success' => false, 'error' => 'Reservation not found']);
?> 