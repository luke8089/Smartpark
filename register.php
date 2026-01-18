<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'components/function.php';
require_once 'components/function_mailer.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (strlen($name) < 2) {
        $error = 'Name must be at least 2 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = db_connect();
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Email already registered.';
        } else {
            if (register_user($name, $email, $password)) {
                // Send welcome email
                send_registration_email($email, $name);
                $_SESSION['register_success'] = 'Registration successful! Please log in.';
                header('Location: reglogin.php');
                exit();
            } else {
                $error = 'Registration failed. Try again.';
            }
        }
        $stmt->close();
        $conn->close();
    }
}
if ($error) {
    $_SESSION['register_error'] = $error;
    header('Location: reglogin.php');
    exit();
}
?> 