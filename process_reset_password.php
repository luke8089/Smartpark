<?php
// Set timezone to UTC for universal compatibility
date_default_timezone_set('UTC');
session_start();
require_once 'components/function.php';
require_once 'components/function_mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!$token || !$password || !$confirm_password) {
        $_SESSION['reset_password_error'] = 'All fields are required.';
        header('Location: reset_password.php?token=' . urlencode($token));
        exit();
    }
    
    if (strlen($password) < 8) {
        $_SESSION['reset_password_error'] = 'Password must be at least 8 characters long.';
        header('Location: reset_password.php?token=' . urlencode($token));
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['reset_password_error'] = 'Passwords do not match.';
        header('Location: reset_password.php?token=' . urlencode($token));
        exit();
    }
    
    // Verify token and get user email
    $conn = db_connect();
    $stmt = $conn->prepare('SELECT email, expires_at, used FROM password_resets WHERE token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset_data = $result->fetch_assoc();
    $stmt->close();
    
    if (!$reset_data) {
        $_SESSION['reset_password_error'] = 'Invalid reset token.';
        header('Location: reset_password.php?token=' . urlencode($token));
        exit();
    }
    
    if ($reset_data['used'] == 1) {
        $_SESSION['reset_password_error'] = 'This reset link has already been used.';
        header('Location: reset_password.php?token=' . urlencode($token));
        exit();
    }
    
    if (strtotime($reset_data['expires_at']) < gmdate('U')) {
        $_SESSION['reset_password_error'] = 'This reset link has expired.';
        header('Location: reset_password.php?token=' . urlencode($token));
        exit();
    }
    
    // Update user password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->bind_param('ss', $hashed_password, $reset_data['email']);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        // Mark token as used
        $stmt = $conn->prepare('UPDATE password_resets SET used = 1 WHERE token = ?');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->close();
        
        // Get user details for email
        $stmt = $conn->prepare('SELECT name FROM users WHERE email = ?');
        $stmt->bind_param('s', $reset_data['email']);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $stmt->close();
        
        // Prepare change details
        $change_details = [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'device_info' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown device',
            'location' => 'Password reset via email link'
        ];
        
        // Send password change confirmation email
        send_password_change_email($reset_data['email'], $user_data['name'], $change_details);
        
        $_SESSION['reset_password_success'] = 'Your password has been successfully reset. You can now log in with your new password.';
        header('Location: reglogin.php');
        exit();
    } else {
        $_SESSION['reset_password_error'] = 'Failed to reset password. Please try again.';
        header('Location: reset_password.php?token=' . urlencode($token));
        exit();
    }
    
    $conn->close();
} else {
    // If someone tries to access this file directly, redirect to login
    header('Location: reglogin.php');
    exit();
}
?> 