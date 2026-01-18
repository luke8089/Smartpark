<?php
require_once "../../config/database.php";
require_once "../../includes/auth.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isLoggedIn()) {
    header("location: ../login.php");
    exit;
}

// Get the latest transaction for the current user
$stmt = $conn->prepare("
    SELECT mt.*, o.total_amount 
    FROM mpesa_transactions mt 
    JOIN orders o ON mt.order_id = o.id 
    WHERE o.user_id = ? 
    ORDER BY mt.created_at DESC 
    LIMIT 1
");
$stmt->execute([$_SESSION['id']]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Pesa Debug Info</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>M-Pesa Debug Information</h2>
        
        <?php if(isset($_SESSION['debug_info'])): ?>
            <div class="alert alert-info">
                <h4>Session Debug Info:</h4>
                <pre><?php print_r($_SESSION['debug_info']); ?></pre>
            </div>
        <?php endif; ?>

        <?php if($transaction): ?>
            <div class="card mt-4">
                <div class="card-header">
                    Latest Transaction Details
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Order ID:</th>
                            <td><?php echo htmlspecialchars($transaction['order_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo htmlspecialchars($transaction['phone']); ?></td>
                        </tr>
                        <tr>
                            <th>Amount:</th>
                            <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                        </tr>
                        <tr>
                            <th>Response Code:</th>
                            <td><?php echo htmlspecialchars($transaction['response_code']); ?></td>
                        </tr>
                        <tr>
                            <th>Response Description:</th>
                            <td><?php echo htmlspecialchars($transaction['response_description']); ?></td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                No transaction found for your account.
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="../my_orders.php" class="btn btn-primary">Back to Orders</a>
        </div>
    </div>
</body>
</html> 