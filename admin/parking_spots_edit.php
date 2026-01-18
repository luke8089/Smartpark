<?php
require_once '../components/function.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? null;
  $name = $_POST['name'] ?? '';
  $location = $_POST['location'] ?? '';
  $features = $_POST['features'] ?? '';
  $price_hourly = $_POST['price_hourly'] ?? 0;
  $price_daily = $_POST['price_daily'] ?? 0;
  $price_weekly = $_POST['price_weekly'] ?? 0;
  $available_spots = $_POST['available_spots'] ?? 0;
  $operating_hours = $_POST['operating_hours'] ?? '';
  $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
  $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;

  $conn = db_connect();
  // Get current image_url
  $stmt = $conn->prepare('SELECT image_url FROM parking_spots WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $current_image_url = $row ? $row['image_url'] : null;
  $stmt->close();

  // Handle image upload
  $image_url = $current_image_url;
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $img = $_FILES['image'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (in_array($img['type'], $allowed)) {
      $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
      $basename = 'spot_' . time() . '_' . rand(1000,9999) . '.' . $ext;
      $target_dir = '../assets/images/';
      if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
      }
      $target_path = $target_dir . $basename;
      if (move_uploaded_file($img['tmp_name'], $target_path)) {
        $image_url = 'assets/images/' . $basename;
      }
    }
  }

  // Update all fields including image_url
  $stmt = $conn->prepare('UPDATE parking_spots SET name=?, location=?, features=?, price_hourly=?, price_daily=?, price_weekly=?, available_spots=?, operating_hours=?, latitude=?, longitude=?, image_url=? WHERE id=?');
  $stmt->bind_param('sssdddissdsi', $name, $location, $features, $price_hourly, $price_daily, $price_weekly, $available_spots, $operating_hours, $latitude, $longitude, $image_url, $id);
  $success = $stmt->execute();
  $stmt->close();
  $conn->close();
  echo json_encode(['success' => $success]);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
  $id = $_GET['id'];
  $conn = db_connect();
  $stmt = $conn->prepare('SELECT * FROM parking_spots WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $spot = $result->fetch_assoc();
  $stmt->close();
  $conn->close();
  if ($spot) {
    echo json_encode(['success' => true, 'spot' => $spot]);
    exit;
  }
  echo json_encode(['success' => false, 'error' => 'Spot not found']);
  exit;
}
echo json_encode(['success' => false, 'error' => 'Invalid request']); 