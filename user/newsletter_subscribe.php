<?php
session_start();

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

require_once '../components/function.php';

try {
    $conn = db_connect();
    
    // Get and validate email
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        throw new Exception('Email is required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $subscriber = $result->fetch_assoc();
        
        if ($subscriber['status'] === 'unsubscribed') {
            // Reactivate subscription
            $update_stmt = $conn->prepare("UPDATE newsletter_subscribers SET status = 'active', unsubscribed_at = NULL, updated_at = NOW() WHERE id = ?");
            $update_stmt->bind_param('i', $subscriber['id']);
            $update_stmt->execute();
            
            $message = 'Welcome back! Your newsletter subscription has been reactivated.';
            $status = 'success';
        } else {
            $message = 'This email is already subscribed to our newsletter.';
            $status = 'error';
        }
    } else {
        // Insert new subscriber
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $insert_stmt = $conn->prepare("
            INSERT INTO newsletter_subscribers 
            (email, user_id, status, subscription_type, source, ip_address, user_agent, created_at) 
            VALUES (?, ?, 'active', 'general', 'website', ?, ?, NOW())
        ");
        $insert_stmt->bind_param('siss', $email, $user_id, $ip_address, $user_agent);
        $insert_stmt->execute();
        
        $message = 'Thank you for subscribing to our newsletter!';
        $status = 'success';
    }
    
    $conn->close();
    
    // Redirect back with status message
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';
    $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'newsletter_status=' . $status . '&newsletter_message=' . urlencode($message);
    
    header('Location: ' . $redirect_url);
    exit();
    
} catch (Exception $e) {
    $message = $e->getMessage();
    $status = 'error';
    
    // Redirect back with error message
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';
    $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'newsletter_status=' . $status . '&newsletter_message=' . urlencode($message);
    
    header('Location: ' . $redirect_url);
    exit();
}
?> 