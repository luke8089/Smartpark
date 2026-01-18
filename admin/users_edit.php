<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../components/function.php';

// Check if required fields are provided
if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['email']) || empty($_POST['role'])) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

$user_id = (int)$_POST['id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$role = $_POST['role'];

// Validate user ID
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit();
}

// Validate role
if (!in_array($role, ['user', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid role']);
    exit();
}

// Validate name length
if (strlen($name) < 2 || strlen($name) > 100) {
    echo json_encode(['success' => false, 'error' => 'Name must be between 2 and 100 characters']);
    exit();
}

try {
    $conn = db_connect();
    
    // Check if user exists and get current profile image
    $check_stmt = $conn->prepare("SELECT id, email, profile_image FROM users WHERE id = ?");
    $check_stmt->bind_param('i', $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user = $result->fetch_assoc();
    $check_stmt->close();
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit();
    }
    
    // Check if email is already taken by another user
    $email_check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_check_stmt->bind_param('si', $email, $user_id);
    $email_check_stmt->execute();
    $email_result = $email_check_stmt->get_result();
    $email_check_stmt->close();
    
    if ($email_result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Email is already taken by another user']);
        exit();
    }
    
    // Handle profile image upload
    $profile_image = $user['profile_image']; // Keep current image by default
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
            exit();
        }
        
        // Validate file size (2MB max)
        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'File size too large. Maximum 2MB allowed']);
            exit();
        }
        
        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user_id . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        
        // Determine upload directory based on user role
        if ($role === 'admin') {
            $upload_dir = '../assets/images/';
            $profile_image = 'assets/images/' . $filename;
        } else {
            $upload_dir = '../user/uploads/';
            $profile_image = 'uploads/' . $filename;
        }
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Move uploaded file
        $upload_path = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // File uploaded successfully, profile_image variable is already set
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
            exit();
        }
    }
    
    // Update the user
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, profile_image = ? WHERE id = ?");
    $update_stmt->bind_param('ssssi', $name, $email, $role, $profile_image, $user_id);
    $success = $update_stmt->execute();
    $update_stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update user']);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 