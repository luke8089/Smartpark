<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}
require_once '../components/function.php';

// Validate required fields
$required = ['name', 'location'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'error' => 'Missing required field: ' . $field]);
        exit();
    }
}

$name = trim($_POST['name']);
$location = trim($_POST['location']);
$features = trim($_POST['features'] ?? '');
$available_spots = (int)($_POST['available_spots'] ?? 0);
$price_hourly = (float)($_POST['price_hourly'] ?? 0);
$price_daily = (float)($_POST['price_daily'] ?? 0);
$price_weekly = (float)($_POST['price_weekly'] ?? 0);
$operating_hours = trim($_POST['operating_hours'] ?? '');
$latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
$longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;

// Handle image upload
$image_url = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $img = $_FILES['image'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($img['type'], $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Invalid image type']);
        exit();
    }
    if ($img['size'] > 2 * 1024 * 1024) { // 2MB limit
        echo json_encode(['success' => false, 'error' => 'Image too large (max 2MB)']);
        exit();
    }
    $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
    $basename = 'spot_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    $target_dir = '../assets/images/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_path = $target_dir . $basename;
    if (!move_uploaded_file($img['tmp_name'], $target_path)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save image']);
        exit();
    }
    $image_url = 'assets/images/' . $basename;
}

// Insert into DB using MySQLi
$conn = db_connect();
$stmt = $conn->prepare("INSERT INTO parking_spots (name, location, features, price_hourly, price_daily, price_weekly, available_spots, operating_hours, latitude, longitude, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    'sssdddissds',
    $name,
    $location,
    $features,
    $price_hourly,
    $price_daily,
    $price_weekly,
    $available_spots,
    $operating_hours,
    $latitude,
    $longitude,
    $image_url
);
$success = $stmt->execute();
if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $stmt->error]);
}
$stmt->close();
$conn->close(); 