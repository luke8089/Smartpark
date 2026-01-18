<?php
// Set timezone to UTC for universal compatibility
date_default_timezone_set('UTC');

// Load configuration
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else {
    die('Configuration file not found. Please copy config.example.php to config.php and update with your credentials.');
}

function db_connect() {
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASS') ? DB_PASS : '';
    $db   = defined('DB_NAME') ? DB_NAME : 'smartpark';
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    return $conn;
}

function register_user($name, $email, $password, $role = 'user') {
    $conn = db_connect();
    $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param('ssss', $name, $email, $hashed, $role);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

function login_user($email, $password) {
    $conn = db_connect();
    $stmt = $conn->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function get_parking_spots($filters = []) {
    $conn = db_connect();
    $sql = "SELECT * FROM parking_spots WHERE 1";
    $params = [];
    $types = '';
    // Filtering logic can be added here later
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $spots = [];
    while ($row = $result->fetch_assoc()) {
        $spots[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $spots;
}

function cleanup_expired_tokens() {
    $conn = db_connect();
    // Use UTC time for universal compatibility
    $stmt = $conn->prepare('DELETE FROM password_resets WHERE expires_at < UTC_TIMESTAMP() OR used = 1');
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function create_reservation($user_id, $spot_id, $spot_number, $license_plate, $vehicle_type, $vehicle_model, $entry_time, $exit_time, $fee, $payment_method, $booking_ref) {
    $conn = db_connect();
    $stmt = $conn->prepare('INSERT INTO reservations (user_id, spot_id, spot_number, license_plate, vehicle_type, vehicle_model, entry_time, exit_time, fee, payment_method, booking_ref) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiissssssds', $user_id, $spot_id, $spot_number, $license_plate, $vehicle_type, $vehicle_model, $entry_time, $exit_time, $fee, $payment_method, $booking_ref);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

function get_reservation_by_ref($booking_ref) {
    $conn = db_connect();
    $stmt = $conn->prepare('SELECT * FROM reservations WHERE booking_ref = ?');
    $stmt->bind_param('s', $booking_ref);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $reservation;
}

function get_reserved_spot_numbers($spot_id) {
    $conn = db_connect();
    $stmt = $conn->prepare('SELECT spot_number FROM reservations WHERE spot_id = ?');
    $stmt->bind_param('i', $spot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reserved = [];
    while ($row = $result->fetch_assoc()) {
        $reserved[] = (int)$row['spot_number'];
    }
    $stmt->close();
    $conn->close();
    return $reserved;
}
