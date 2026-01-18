<?php
// Set timezone to UTC for universal compatibility
date_default_timezone_set('UTC');
session_start();
require_once 'components/function.php';
require_once 'components/function_mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_password_error'] = 'Please enter a valid email address.';
        header('Location: forgot_password.php');
        exit();
    }
    
    // Check if user exists
    $conn = db_connect();
    $stmt = $conn->prepare('SELECT id, name FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        // Don't reveal if email exists or not for security
        $_SESSION['forgot_password_success'] = 'If an account with that email exists, we have sent a password reset link.';
        header('Location: forgot_password.php');
        exit();
    }
    
    // Generate unique token
    $token = bin2hex(random_bytes(32));
    // Use UTC timezone for universal compatibility
    $expires_at = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Clean up expired tokens first
    cleanup_expired_tokens();
    
    // Delete any existing reset tokens for this email
    $stmt = $conn->prepare('DELETE FROM password_resets WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
    
    // Insert new reset token
    $stmt = $conn->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $email, $token, $expires_at);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    if ($result) {
        // Send password reset email
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset_password.php?token=" . $token;
        
        if (send_password_reset_email($email, $user['name'], $reset_link)) {
            $_SESSION['forgot_password_success'] = 'Password reset link has been sent to your email address. Please check your inbox and spam folder.';
        } else {
            $_SESSION['forgot_password_error'] = 'Failed to send reset email. Please try again later.';
        }
    } else {
        $_SESSION['forgot_password_error'] = 'An error occurred. Please try again later.';
    }
    
    header('Location: forgot_password.php');
    exit();
} else {
    // If someone tries to access this file directly, redirect to forgot password page
    header('Location: forgot_password.php');
    exit();
}
?> 