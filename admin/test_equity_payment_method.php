<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reglogin.php");
    exit();
}

require_once '../components/function.php';

$conn = db_connect();

echo "<h2>Equity Payment Method Test</h2>";

// Check all reservations with equity payments
$stmt = $conn->prepare("
    SELECT r.id, r.booking_ref, r.payment_method, r.license_plate, 
           ep.status as equity_status, ep.created_at as equity_created
    FROM reservations r 
    LEFT JOIN equity_payments ep ON r.id = ep.reservation_id
    WHERE r.payment_method = 'equity' OR ep.id IS NOT NULL
    ORDER BY r.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Reservation ID</th><th>Booking Ref</th><th>Payment Method</th><th>License Plate</th><th>Equity Status</th><th>Equity Created</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['booking_ref'] . "</td>";
    echo "<td style='background-color: " . ($row['payment_method'] === 'equity' ? '#90EE90' : '#FFB6C1') . "'>" . $row['payment_method'] . "</td>";
    echo "<td>" . $row['license_plate'] . "</td>";
    echo "<td>" . ($row['equity_status'] ?? 'N/A') . "</td>";
    echo "<td>" . ($row['equity_created'] ?? 'N/A') . "</td>";
    echo "</tr>";
}

echo "</table>";

$stmt->close();
$conn->close();
?> 