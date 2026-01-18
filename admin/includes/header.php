<?php
// session_start(); // Uncomment if needed for admin session
// Fetch admin name and (optionally) profile image
$admin_name = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Admin';
$profile_image_url = isset($_SESSION['user']['profile_image']) && $_SESSION['user']['profile_image'] ? $_SESSION['user']['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin_name) . '&background=20215B&color=fff&size=128';
// Fix image path for local images
if (!preg_match('/^https?:\/\//', $profile_image_url)) {
  $profile_image_url = '/smartpark/' . ltrim($profile_image_url, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartPark Admin</title>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#20215B'
          }
        }
      }
    }
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
</head>
<body>
<header class="bg-white shadow-sm sticky top-0 z-40">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16 items-center">
      <div class="flex items-center">
        <a href="admin_dashboard.php" class="text-2xl font-['Pacifico'] text-primary">SmartPark Admin</a>
      </div>
      <div class="flex items-center space-x-4 relative">
        <!-- Hamburger for mobile -->
        <button id="headerSidebarToggle" class="md:hidden text-primary text-3xl focus:outline-none mr-2">
          <i class="ri-menu-line"></i>
        </button>
        <!-- Profile Dropdown -->
        <div class="relative">
          <button id="adminProfileBtn" class="flex items-center space-x-2 focus:outline-none">
            <img src="<?php echo $profile_image_url; ?>" alt="Profile" class="w-9 h-9 rounded-full border-2 border-primary object-cover">
            <span class="hidden sm:inline text-primary font-medium"><?php echo htmlspecialchars($admin_name); ?></span>
            <i class="ri-arrow-down-s-line text-xl text-primary"></i>
          </button>
          <div id="adminProfileDropdown" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border z-50">
            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-primary/10 hover:text-primary">My Profile</a>
            <a href="../logout.php" class="block px-4 py-2 text-red-500 hover:bg-red-100">Logout</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Dropdown sidebar links for mobile -->
    <div id="headerSidebarDropdown" class="md:hidden hidden absolute left-0 right-0 bg-white shadow-lg border-t z-50">
      <nav class="p-4 space-y-2">
        <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'admin_dashboard.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
          <i class="ri-dashboard-line"></i><span>Dashboard</span>
        </a>
        <a href="parking_spots.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'parking_spots.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
          <i class="ri-parking-line"></i><span>Parking Spots</span>
        </a>
        <a href="reservations.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'reservations.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
          <i class="ri-calendar-check-line"></i><span>Reservations</span>
        </a>
        <a href="users.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'users.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
          <i class="ri-user-3-line"></i><span>Users</span>
        </a>
        <a href="payments.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'payments.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
          <i class="ri-bank-card-line"></i><span>Payments</span>
        </a>
        <a href="reports.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'reports.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
          <i class="ri-bar-chart-2-line"></i><span>Reports</span>
        </a>
      </nav>
    </div>
  </nav>
</header>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var toggle = document.getElementById('headerSidebarToggle');
  var dropdown = document.getElementById('headerSidebarDropdown');
  if (toggle && dropdown) {
    toggle.addEventListener('click', function() {
      dropdown.classList.toggle('hidden');
    });
    // Optional: close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
        dropdown.classList.add('hidden');
      }
    });
  }
  // Profile dropdown
  var profileBtn = document.getElementById('adminProfileBtn');
  var profileDropdown = document.getElementById('adminProfileDropdown');
  if (profileBtn && profileDropdown) {
    profileBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      profileDropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', function(e) {
      if (!profileDropdown.contains(e.target) && !profileBtn.contains(e.target)) {
        profileDropdown.classList.add('hidden');
      }
    });
  }
});
</script>
