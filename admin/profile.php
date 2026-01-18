<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reglogin.php");
    exit();
}
// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../components/function.php';
require_once '../components/function_mailer.php';

$admin_id = $_SESSION['user_id'];
$conn = db_connect();
$msg = '';
$err = '';
// Fetch admin data
$stmt = $conn->prepare('SELECT name, email, profile_image, password FROM users WHERE id=? AND role="admin"');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$stmt->bind_result($name, $email, $profile_image, $hashed_password);
$stmt->fetch();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $profile_image_url = $profile_image;
    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $upload_dir = __DIR__ . '/../user/uploads/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $filename = 'profile_' . $admin_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;
        $web_path = 'user/uploads/' . $filename;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
            $profile_image_url = $web_path;
        }
    }
    $stmt = $conn->prepare('UPDATE users SET name=?, email=?, profile_image=? WHERE id=?');
    $stmt->bind_param('sssi', $new_name, $new_email, $profile_image_url, $admin_id);
    if ($stmt->execute()) {
        $msg = 'Profile updated successfully!';
        $_SESSION['user']['name'] = $new_name;
        $_SESSION['user']['email'] = $new_email;
        $_SESSION['user']['profile_image'] = $profile_image_url;
        $name = $new_name;
        $email = $new_email;
        $profile_image = $profile_image_url;
    } else {
        $err = 'Failed to update profile.';
    }
    $stmt->close();
}
// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (!password_verify($current_password, $hashed_password)) {
        $err = 'Current password is incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $err = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $err = 'New password must be at least 6 characters.';
    } else {
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password=? WHERE id=?');
        $stmt->bind_param('si', $new_hashed, $admin_id);
        if ($stmt->execute()) {
            // Send password change confirmation email
            $change_details = [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'device_info' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown device',
                'location' => 'Admin profile settings page'
            ];
            
            send_password_change_email($email, $name, $change_details);
            
            $msg = 'Password changed successfully! Check your email for confirmation.';
        } else {
            $err = 'Failed to change password.';
        }
        $stmt->close();
    }
}
$conn->close();
$profile_image_url = $profile_image ? $profile_image : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=20215B&color=fff&size=128';
include 'includes/header.php';
?>

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
<main class="max-w-2xl mx-auto px-4 py-12">
  <h1 class="text-3xl font-bold mb-8 text-center font-['Pacifico'] text-primary fade-in">My Profile</h1>
  <?php if ($msg): ?><div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-6 text-center fade-in"><?php echo $msg; ?></div><?php endif; ?>
  <?php if ($err): ?><div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-6 text-center fade-in"><?php echo $err; ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-8 fade-in" style="animation-delay:0.1s">
    <div class="flex flex-col items-center mb-8">
      <div class="relative group">
        <?php
          // If the profile_image_url starts with http, use as is (external)
          if (preg_match('/^https?:\/\//', $profile_image_url)) {
            $imgSrc = $profile_image_url;
          } else {
            $imgSrc = '/smartpark/' . ltrim($profile_image_url, '/');
          }
        ?>
        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Profile Image" class="w-32 h-32 rounded-full border-4 border-primary shadow-lg object-cover transition-transform group-hover:scale-105">
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
    <button type="submit" name="update_profile" class="w-full bg-primary text-white py-3 rounded-[10px] hover:bg-primary/80 transition-colors !rounded-button whitespace-nowrap cursor-pointer text-lg font-semibold">
  Update Profile
</button>

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
    <button type="submit" name="change_password" class="w-full bg-primary text-white py-3 rounded-[10px] hover:bg-primary/80 transition-colors !rounded-button whitespace-nowrap cursor-pointer text-lg font-semibold">Change Password</button>
  </form>
</main>
<script>
document.getElementById('profile_image')?.addEventListener('change', function() {
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      this.closest('.group').querySelector('img').src = e.target.result;
    };
    reader.readAsDataURL(this.files[0]);
  }
});
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.fade-in').forEach((el, i) => {
    el.style.animationDelay = (i * 0.1 + 0.1) + 's';
  });
});
</script>
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
<?php include 'includes/footer.php'; ?>
