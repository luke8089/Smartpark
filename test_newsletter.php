<?php
session_start();
require_once 'components/function.php';

// Test database connection and newsletter table
try {
    $conn = db_connect();
    
    // Check if newsletter_subscribers table exists
    $result = $conn->query("SHOW TABLES LIKE 'newsletter_subscribers'");
    $table_exists = $result->num_rows > 0;
    
    // Get subscriber count
    $subscriber_count = 0;
    if ($table_exists) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM newsletter_subscribers");
        $subscriber_count = $count_result->fetch_assoc()['count'];
    }
    
    $conn->close();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Test - SmartPark</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8 text-primary">Newsletter Subscription Test</h1>
        
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Database Status</h2>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php else: ?>
                <div class="space-y-2 mb-6">
                    <p><strong>Table exists:</strong> 
                        <span class="<?php echo $table_exists ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $table_exists ? '✅ Yes' : '❌ No'; ?>
                        </span>
                    </p>
                    <p><strong>Subscriber count:</strong> 
                        <span class="text-blue-600 font-semibold"><?php echo $subscriber_count; ?></span>
                    </p>
                </div>
            <?php endif; ?>
            
            <h2 class="text-xl font-semibold mb-4">Test Newsletter Form</h2>
            <form method="POST" action="user/newsletter_subscribe.php" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="test@example.com">
                </div>
                <button type="submit" 
                        class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-primary/90 transition">
                    Subscribe to Newsletter
                </button>
            </form>
            
            <?php if (isset($_GET['newsletter_status'])): ?>
                <div class="mt-4 p-3 rounded-md <?php echo $_GET['newsletter_status'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($_GET['newsletter_message'] ?? ''); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-8 text-center">
            <a href="user/index.php" class="text-primary hover:underline">← Back to User Dashboard</a>
        </div>
    </div>
</body>
</html> 