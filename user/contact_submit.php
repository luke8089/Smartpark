<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
$user_id = null;
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

// Validate required fields
$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(['success' => false, 'error' => ucfirst($field) . ' is required']);
        exit();
    }
}

// Sanitize and validate input
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
$subject = trim($_POST['subject']);
$message = trim($_POST['message']);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit();
}

// Validate name length
if (strlen($name) > 100) {
    echo json_encode(['success' => false, 'error' => 'Name is too long (maximum 100 characters)']);
    exit();
}

// Validate subject length
if (strlen($subject) > 255) {
    echo json_encode(['success' => false, 'error' => 'Subject is too long (maximum 255 characters)']);
    exit();
}

// Validate message length
if (strlen($message) > 65535) {
    echo json_encode(['success' => false, 'error' => 'Message is too long']);
    exit();
}

// Get IP address
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

// Include database connection
require_once '../components/function.php';
$conn = db_connect();

// Insert into database
$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, user_id, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sssssis', $name, $email, $phone, $subject, $message, $user_id, $ip_address);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Your message has been sent successfully! We will get back to you soon.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send message. Please try again.']);
}

$stmt->close();
$conn->close();
?> 