<?php
// session_start(); // Removed to avoid duplicate session warnings
// Determine the current page for menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$profile_image_url = null;

// Debug: Check if session is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
  require_once __DIR__ . '/../../components/function.php';
  $conn = db_connect();
  $stmt = $conn->prepare('SELECT profile_image, name FROM users WHERE id = ?');
  $stmt->bind_param('i', $_SESSION['user_id']);
  $stmt->execute();
  $stmt->bind_result($profile_image_db, $profile_name_db);
  $stmt->fetch();
  $stmt->close();
  $conn->close();
  if ($profile_image_db) {
    $profile_image_url = $profile_image_db;
  } else {
    $profile_image_url = 'https://ui-avatars.com/api/?name=' . urlencode($profile_name_db) . '&background=20215B&color=fff&size=128';
  }
}
?>
<header class="bg-white shadow-sm sticky top-0 z-40">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16 items-center">
      <div class="flex items-center">
        <a href="../index.php" class="text-2xl font-['Pacifico'] text-primary font-bold tracking-wide">SmartPark Innovations</a>
        <!-- Hamburger for mobile -->
        <button id="mobileMenuBtn" class="ml-4 md:hidden flex items-center justify-center p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary" aria-label="Open menu" type="button">
          <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div id="mainNav" class="hidden md:flex items-center space-x-8 ml-10">
          <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'text-primary font-bold underline' : 'text-primary font-medium'; ?>">Home</a>
          <a href="about.php" class="<?php echo $current_page == 'about.php' ? 'text-primary font-bold underline' : 'text-gray-500 hover:text-primary'; ?>">About</a>
          <a href="service.php" class="<?php echo $current_page == 'service.php' ? 'text-primary font-bold underline' : 'text-gray-500 hover:text-primary'; ?>">Services</a>
          <a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'text-primary font-bold underline' : 'text-gray-500 hover:text-primary'; ?>">Contact</a>
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/smartpark/user/messages.php" class="flex items-center px-4 py-2 <?php echo $current_page == 'messages.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-primary hover:text-white'; ?> rounded transition">
              <i class="ri-message-3-line mr-2 text-lg"></i>
              <span>Messages</span>
            </a>
          <?php else: ?>
            <a href="../reglogin.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-primary hover:text-white rounded transition">
              <i class="ri-message-3-line mr-2 text-lg"></i>
              <span>Messages</span>
            </a>
          <?php endif; ?>
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="receiptproduced.php" class="<?php echo ($current_page == 'receiptproduced.php' || $current_page == 'receipt.php') ? 'text-primary font-bold underline' : 'text-gray-500 hover:text-primary'; ?>">Receipts</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <?php if (isset($_SESSION['user'])): ?>
          <!-- Profile Icon with Dropdown -->
          <div class="relative">
            <button onclick="toggleProfileMenu()" class="focus:outline-none">
              <img src="<?php echo isset($profile_image_url) ? $profile_image_url : 'https://ui-avatars.com/api/?name=User&background=20215B&color=fff&size=128'; ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-primary">
            </button>
            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
              <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
              <a href="../logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-100">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="../reglogin.php"><button onclick="showLoginModal()" class="text-gray-500 hover:text-primary font-medium cursor-pointer whitespace-nowrap">Login</button></a>
          <a href="../reglogin.php?register=1"><button onclick="showRegisterModal()" class="bg-primary text-white px-6 py-2 !rounded-button cursor-pointer whitespace-nowrap">Register</button></a>
        <?php endif; ?>
      </div>
    </div>
    <!-- Mobile menu -->
    <div id="mobileNav" class="md:hidden hidden flex-col space-y-2 mt-2 pb-4">
      <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'block text-primary font-bold underline' : 'block text-primary font-medium'; ?>">Home</a>
      <a href="about.php" class="<?php echo $current_page == 'about.php' ? 'block text-primary font-bold underline' : 'block text-gray-500 hover:text-primary'; ?>">About</a>
      <a href="service.php" class="<?php echo $current_page == 'service.php' ? 'block text-primary font-bold underline' : 'block text-gray-500 hover:text-primary'; ?>">Services</a>
      <a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'block text-primary font-bold underline' : 'block text-gray-500 hover:text-primary'; ?>">Contact</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/smartpark/user/messages.php" class="block <?php echo $current_page == 'messages.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-primary hover:text-white'; ?> rounded transition">
          <i class="ri-message-3-line mr-2 text-lg"></i>
          <span>Messages</span>
        </a>
      <?php else: ?>
        <a href="../reglogin.php" class="block text-gray-700 hover:bg-primary hover:text-white rounded transition">
          <i class="ri-message-3-line mr-2 text-lg"></i>
          <span>Messages</span>
        </a>
      <?php endif; ?>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="receiptproduced.php" class="<?php echo ($current_page == 'receiptproduced.php' || $current_page == 'receipt.php') ? 'block text-primary font-bold underline' : 'block text-gray-500 hover:text-primary'; ?>">Receipts</a>
      <?php endif; ?>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="user.php" class="block text-gray-500 hover:text-primary">Profile</a>
        <a href="../logout.php" class="block text-red-500 hover:bg-gray-100">Logout</a>
      <?php else: ?>
        <a href="reglogin.php" class="block text-gray-500 hover:text-primary">Login</a>
        <a href="reglogin.php?register=1" class="block bg-primary text-white px-6 py-2 !rounded-button cursor-pointer whitespace-nowrap text-center">Register</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
<script>
function showLoginModal() {
  if (document.getElementById('loginModal'))
    document.getElementById('loginModal').classList.remove('hidden');
}
function closeLoginModal() {
  if (document.getElementById('loginModal'))
    document.getElementById('loginModal').classList.add('hidden');
}
function showRegisterModal() {
  if (document.getElementById('registerModal'))
    document.getElementById('registerModal').classList.remove('hidden');
}
function closeRegisterModal() {
  if (document.getElementById('registerModal'))
    document.getElementById('registerModal').classList.add('hidden');
}
function toggleProfileMenu() {
  var menu = document.getElementById('profileMenu');
  if (menu) menu.classList.toggle('hidden');
}
// Hamburger menu logic
let mobileMenuOpen = false;
document.getElementById('mobileMenuBtn').addEventListener('click', function(e) {
  e.stopPropagation();
  var nav = document.getElementById('mobileNav');
  mobileMenuOpen = !mobileMenuOpen;
  if (nav) nav.classList.toggle('hidden', !mobileMenuOpen);
});
document.addEventListener('click', function(e) {
  var profileMenu = document.getElementById('profileMenu');
  if (profileMenu && !profileMenu.classList.contains('hidden')) {
    const isClickInside = profileMenu.contains(e.target) || (e.target.closest && e.target.closest('button'));
    if (!isClickInside) profileMenu.classList.add('hidden');
  }
  // Close mobile menu if click outside
  var mobileNav = document.getElementById('mobileNav');
  var mobileMenuBtn = document.getElementById('mobileMenuBtn');
  if (mobileNav && mobileMenuOpen) {
    if (!mobileNav.contains(e.target) && e.target !== mobileMenuBtn) {
      mobileNav.classList.add('hidden');
      mobileMenuOpen = false;
    }
  }
});
</script>
