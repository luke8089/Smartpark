<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<aside class="w-52 bg-white shadow-lg flex flex-col min-h-0 hidden md:block md:static fixed top-0 left-0 h-full z-30">
  <div class="p-6 border-b">
    <a href="admin_dashboard.php" class="text-2xl font-['Pacifico'] text-primary">SmartPark Admin</a>
  </div>
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
      <i class="ri-bank-card-line"></i><span>M-Pesa Payments</span>
    </a>
    <a href="equity_payments.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'equity_payments.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
      <i class="ri-bank-line"></i><span>Equity Payments</span>
    </a>
    <a href="reports.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'reports.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
      <i class="ri-bar-chart-2-line"></i><span>Reports</span>
    </a>
    <a href="messages.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'messages.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
      <i class="ri-message-3-line"></i><span>Messages</span>
    </a>
    <a href="inbox.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition <?php echo $current_page == 'inbox.php' ? 'text-primary bg-primary/10' : 'text-gray-700 hover:bg-primary/10 hover:text-primary'; ?>">
      <i class="ri-inbox-line"></i><span>Inbox</span>
    </a>
  </nav>
</aside> 