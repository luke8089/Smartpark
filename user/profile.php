<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ../reglogin.php');
  exit();
}
// Prevent page caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require_once '../components/function.php';
require_once '../components/function_mailer.php';
$user_id = $_SESSION['user']['id'];
$conn = db_connect();
// Fetch user info
$stmt = $conn->prepare('SELECT name, email, profile_image FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $profile_image);
$stmt->fetch();
$stmt->close();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_name = trim($_POST['name']);
  $new_email = trim($_POST['email']);
  $img_path = $profile_image;
  if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
    $target = 'uploads/profile_' . $user_id . '_' . time() . '.' . $ext;
    if (!is_dir('uploads')) mkdir('uploads', 0777, true);
    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
      $img_path = $target;
    } else {
      $error = 'Failed to upload image.';
    }
  }
  // Password change logic
  $change_password = isset($_POST['current_password']) && $_POST['current_password'] !== '';
  if ($change_password) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    // Fetch current hash
    $conn2 = db_connect();
    $stmt2 = $conn2->prepare('SELECT password FROM users WHERE id = ?');
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $stmt2->bind_result($current_hash);
    $stmt2->fetch();
    $stmt2->close();
    $conn2->close();
    if (!password_verify($current_password, $current_hash)) {
      $error = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
      $error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
      $error = 'New passwords do not match.';
    } else {
      $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
      $conn2 = db_connect();
      $stmt2 = $conn2->prepare('UPDATE users SET password=? WHERE id=?');
      $stmt2->bind_param('si', $new_hash, $user_id);
      if ($stmt2->execute()) {
        // Send password change confirmation email
        $change_details = [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'device_info' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown device',
            'location' => 'Profile settings page'
        ];
        
        send_password_change_email($email, $name, $change_details);
        
        $success = 'Password updated successfully! Check your email for confirmation.';
      } else {
        $error = 'Failed to update password.';
      }
      $stmt2->close();
      $conn2->close();
    }
  }
  if (!$error && !$change_password) {
    $stmt = $conn->prepare('UPDATE users SET name=?, email=?, profile_image=? WHERE id=?');
    $stmt->bind_param('sssi', $new_name, $new_email, $img_path, $user_id);
    if ($stmt->execute()) {
      $success = 'Profile updated successfully!';
      $name = $new_name;
      $email = $new_email;
      $profile_image = $img_path;
      $_SESSION['user']['name'] = $name;
      $_SESSION['user']['email'] = $email;
    } else {
      $error = 'Failed to update profile.';
    }
    $stmt->close();
  }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - SmartPark Innovations</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: '#20215B',
          secondary: '#64748B'
        },
        borderRadius: {
          'none': '0px',
          'sm': '4px',
          DEFAULT: '8px',
          'md': '12px',
          'lg': '16px',
          'xl': '20px',
          '2xl': '24px',
          '3xl': '32px',
          'full': '9999px',
          'button': '8px'
        }
      }
    }
  }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    .fade-in {
      opacity: 0;
      transform: translateY(30px) scale(0.98);
      animation: fadeInUp 0.7s cubic-bezier(.4,2,.6,1) forwards;
    }
    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: none;
      }
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include 'includes/header.php'; ?>
<main class="max-w-2xl mx-auto px-4 py-12">
  <h1 class="text-3xl font-bold mb-8 text-center font-['Pacifico'] text-primary fade-in">My Profile</h1>
  <?php if ($success): ?>
    <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-6 text-center fade-in"><?php echo $success; ?></div>
  <?php elseif ($error): ?>
    <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-6 text-center fade-in"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-8 fade-in" style="animation-delay:0.1s">
    <div class="flex flex-col items-center mb-8">
      <div class="relative group">
        <img src="<?php echo $profile_image ? $profile_image : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=20215B&color=fff&size=128'; ?>" alt="Profile Image" class="w-32 h-32 rounded-full border-4 border-primary shadow-lg object-cover transition-transform group-hover:scale-105">
        <label for="profile_image" class="absolute bottom-2 right-2 bg-primary text-white p-2 rounded-full shadow cursor-pointer hover:bg-primary/80 transition"><i class="ri-camera-line"></i></label>
        <input type="file" id="profile_image" name="profile_image" accept="image/*" class="hidden">
      </div>
    </div>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Name</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20">
    </div>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20">
    </div>
    <button type="submit" class="w-full bg-primary text-white py-3 rounded-[10px] hover:bg-primary/80 transition-colors !rounded-button whitespace-nowrap cursor-pointer text-lg font-semibold mb-8">Update Profile</button>
  </form>
  <form method="post" class="bg-white rounded-xl shadow-lg p-8 fade-in mt-8" style="animation-delay:0.2s">
    <h2 class="text-xl font-semibold mb-6 text-primary flex items-center"><i class="ri-lock-password-line mr-2"></i>Change Password</h2>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Current Password</label>
      <input type="password" name="current_password" class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20" required>
    </div>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">New Password</label>
      <input type="password" name="new_password" class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20" required>
    </div>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Confirm New Password</label>
      <input type="password" name="confirm_password" class="w-full px-4 py-2 border rounded-button focus:outline-none focus:ring-2 focus:ring-primary/20" required>
    </div>
    <button type="submit" class="w-full bg-primary text-white py-3 rounded-[10px] hover:bg-primary/80 transition-colors !rounded-button whitespace-nowrap cursor-pointer text-lg font-semibold">Change Password</button>
  </form>
</main>
<?php include 'includes/footer.php'; ?>
<script>
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.fade-in').forEach((el, i) => {
    el.style.animationDelay = (i * 0.1 + 0.1) + 's';
  });
});
</script>
</body>
</html>
