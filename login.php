<?php
session_start();
require_once 'components/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$email || !$password) {
        $_SESSION['login_error'] = 'Email and password are required.';
        header('Location: reglogin.php');
        exit();
    } else {
        $user = login_user($email, $password);
        if ($user) {
            $_SESSION['user'] = [ 'id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role'] ];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header('Location: admin/admin_dashboard.php');
            } else {
                header('Location: user/index.php');
            }
            exit();
        } else {
            $_SESSION['login_error'] = 'Invalid email or password. Please try again.';
            header('Location: reglogin.php');
            exit();
        }
    }
} else {
    // If someone tries to access login.php directly, redirect to the login page
    header('Location: reglogin.php');
    exit();
}
?> 