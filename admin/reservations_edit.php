<?php
// Start output buffering to catch any unexpected output
ob_start();

// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// Debug: Log received data
error_log("=== RESERVATIONS_EDIT.PHP START ===");
error_log("Received POST data: " . print_r($_POST, true));
error_log("Payment method value: " . (isset($_POST['payment_method']) ? $_POST['payment_method'] : 'NOT SET'));

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    error_log("Unauthorized access attempt");
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    require_once '../components/function.php';
    error_log("Function.php included successfully");
} catch (Exception $e) {
    error_log("Error including function.php: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'System error: ' . $e->getMessage()]);
    exit();
}

// Check if required fields are provided with detailed error messages
$missing_fields = [];
if (empty($_POST['id'])) $missing_fields[] = 'id';
if (empty($_POST['license_plate'])) $missing_fields[] = 'license_plate';
if (empty($_POST['vehicle_type'])) $missing_fields[] = 'vehicle_type';
if (empty($_POST['vehicle_model'])) $missing_fields[] = 'vehicle_model';
if (empty($_POST['entry_time'])) $missing_fields[] = 'entry_time';
if (empty($_POST['exit_time'])) $missing_fields[] = 'exit_time';
if (empty($_POST['fee'])) $missing_fields[] = 'fee';
if (!isset($_POST['payment_method']) || $_POST['payment_method'] === '') $missing_fields[] = 'payment_method';

if (!empty($missing_fields)) {
    error_log("Missing fields: " . implode(', ', $missing_fields));
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
    exit();
}

error_log("All required fields present");

$reservation_id = (int)$_POST['id'];
$license_plate = trim($_POST['license_plate']);
$vehicle_type = $_POST['vehicle_type'];
$vehicle_model = trim($_POST['vehicle_model']);
$entry_time = $_POST['entry_time'];
$exit_time = $_POST['exit_time'];
$fee = (float)$_POST['fee'];
$payment_method = $_POST['payment_method'];
$spot_number = !empty($_POST['spot_number']) ? (int)$_POST['spot_number'] : null;

error_log("Variables processed: ID=$reservation_id, Plate=$license_plate, Type=$vehicle_type, Method=$payment_method");

// Validate reservation ID
if ($reservation_id <= 0) {
    error_log("Invalid reservation ID: $reservation_id");
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid reservation ID']);
    exit();
}

// Validate license plate
if (strlen($license_plate) < 2 || strlen($license_plate) > 50) {
    error_log("Invalid license plate length: " . strlen($license_plate));
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'License plate must be between 2 and 50 characters']);
    exit();
}

// Validate vehicle type
$allowed_vehicle_types = ['car', 'suv', 'truck', 'motorcycle'];
if (!in_array($vehicle_type, $allowed_vehicle_types)) {
    error_log("Invalid vehicle type: $vehicle_type");
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid vehicle type']);
    exit();
}

// Validate vehicle model
if (strlen($vehicle_model) < 1 || strlen($vehicle_model) > 100) {
    error_log("Invalid vehicle model length: " . strlen($vehicle_model));
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Vehicle model must be between 1 and 100 characters']);
    exit();
}

// Validate fee
if ($fee < 0) {
    error_log("Invalid fee: $fee");
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Fee cannot be negative']);
    exit();
}

// Validate payment method
$allowed_payment_methods = ['mpesa', 'equity', '0'];
if (!in_array($payment_method, $allowed_payment_methods)) {
    error_log("Invalid payment method: $payment_method");
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid payment method']);
    exit();
}

// Validate dates
try {
    $entry_datetime = new DateTime($entry_time);
    $exit_datetime = new DateTime($exit_time);
    $now = new DateTime();

    if ($entry_datetime >= $exit_datetime) {
        error_log("Invalid date range: entry=$entry_time, exit=$exit_time");
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Exit time must be after entry time']);
        exit();
    }
    error_log("Date validation passed");
} catch (Exception $e) {
    error_log("Date validation error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid date format: ' . $e->getMessage()]);
    exit();
}

error_log("Starting database operations");

try {
    $conn = db_connect();
    error_log("Database connected successfully");
    
    // Check if reservation exists
    $check_stmt = $conn->prepare("SELECT id FROM reservations WHERE id = ?");
    $check_stmt->bind_param('i', $reservation_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $reservation = $result->fetch_assoc();
    $check_stmt->close();
    
    if (!$reservation) {
        error_log("Reservation not found: $reservation_id");
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Reservation not found']);
        exit();
    }
    
    error_log("Reservation found, proceeding with update");
    
    // Update the reservation
    $update_stmt = $conn->prepare("UPDATE reservations SET license_plate = ?, vehicle_type = ?, vehicle_model = ?, entry_time = ?, exit_time = ?, fee = ?, payment_method = ?, spot_number = ? WHERE id = ?");
    $update_stmt->bind_param('ssssdsii', $license_plate, $vehicle_type, $vehicle_model, $entry_time, $exit_time, $fee, $payment_method, $spot_number, $reservation_id);
    $success = $update_stmt->execute();
    $update_stmt->close();
    
    if ($success) {
        error_log("Reservation updated successfully");
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Reservation updated successfully']);
    } else {
        error_log("Failed to update reservation: " . $conn->error);
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Failed to update reservation: ' . $conn->error]);
    }
    
    $conn->close();
    error_log("Database connection closed");
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

error_log("=== RESERVATIONS_EDIT.PHP END ===");
?> 