<?php
header('Content-Type: application/json');
require_once '../../components/function.php';

$booking_ref = $_GET['booking_ref'] ?? '';
$spot_id = isset($_GET['spot_id']) ? intval($_GET['spot_id']) : (isset($_SESSION['spot_id']) ? intval($_SESSION['spot_id']) : 0);
if (!$booking_ref) {
    echo json_encode(['status' => 'not_found']);
    exit;
}
$reservation = get_reservation_by_ref($booking_ref);
if (!$reservation) {
    echo json_encode(['status' => 'not_found']);
    exit;
}
$reservation_id = $reservation['id'];
$conn = db_connect();
$stmt = $conn->prepare('SELECT status FROM mpesa_payments WHERE reservation_id = ? ORDER BY id DESC LIMIT 1');
$stmt->bind_param('i', $reservation_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
$conn->close();
$response = ['status' => 'not_found'];
if ($row && $row['status'] === 'completed') {
    $response['status'] = 'completed';
} else if ($row && $row['status'] === 'failed') {
    $response['status'] = 'failed';
} else if ($row) {
    $response['status'] = 'pending';
}
if ($spot_id) {
    $response['spot_id'] = $spot_id;
}
echo json_encode($response); 