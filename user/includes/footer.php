<footer class="bg-gray-900 text-white py-12">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
<div>
<h3 class="font-['Pacifico'] text-2xl mb-4 font-bold tracking-wide">SmartPark Innovations</h3>
<p class="text-gray-400">Smart parking solutions for modern cities.</p>
</div>
<div>
<h4 class="font-semibold mb-4">Quick Links</h4>
<ul class="space-y-2">
<li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
<li><a href="#" class="text-gray-400 hover:text-white">Services</a></li>
<li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
<li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
</ul>
</div>
<div>
<h4 class="font-semibold mb-4">Contact</h4>
<ul class="space-y-2">
<li class="text-gray-400">123 Innovation Street</li>
<li class="text-gray-400">Smart City, SC 12345</li>
<li class="text-gray-400">contact@smartpark.com</li>
<li class="text-gray-400">+1 (555) 123-4567</li>
</ul>
</div>
<div>
<h4 class="font-semibold mb-4">Newsletter</h4>
<form method="POST" action="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/user/') !== false ? 'newsletter_subscribe.php' : 'user/newsletter_subscribe.php'; ?>" class="space-y-4">
<input type="email" name="email" placeholder="Enter your email" class="w-full px-4 py-2 rounded-lg bg-gray-800 border-none text-white focus:ring-2 focus:ring-primary focus:outline-none" required>
<button type="submit" class="w-full bg-primary text-white py-2 !rounded-button cursor-pointer whitespace-nowrap hover:bg-primary/90 transition">
  Subscribe
</button>
</form>
<?php if (isset($_GET['newsletter_status'])): ?>
  <div class="mt-3 text-sm <?php echo $_GET['newsletter_status'] === 'success' ? 'text-green-400' : 'text-red-400'; ?>">
    <?php echo htmlspecialchars($_GET['newsletter_message'] ?? ''); ?>
  </div>
<?php endif; ?>
</div>
</div>
<div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
<p class="text-gray-400">&copy; 2025 SmartPark Innovations. All rights reserved.</p>
<div class="flex space-x-6 mt-4 md:mt-0">
<a href="#" class="text-gray-400 hover:text-white">
<i class="ri-facebook-fill text-xl"></i>
</a>
<a href="#" class="text-gray-400 hover:text-white">
<i class="ri-twitter-fill text-xl"></i>
</a>
<a href="#" class="text-gray-400 hover:text-white">
<i class="ri-linkedin-fill text-xl"></i>
</a>
<a href="#" class="text-gray-400 hover:text-white">
<i class="ri-instagram-fill text-xl"></i>
</a>
</div>
</div>
</div>
</footer>
<script>

function showToast(message) {
  var toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg toast';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}
</script>
