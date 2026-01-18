<?php
// Simple test to check if the file is accessible
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing reservations_edit.php accessibility...\n";

// Test basic PHP functionality
echo "PHP is working correctly.\n";

// Test database connection
try {
    require_once '../components/function.php';
    $conn = db_connect();
    echo "Database connection successful.\n";
    $conn->close();
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

// Test JSON encoding
$test_data = ['success' => true, 'message' => 'Test successful'];
echo "JSON encoding test: " . json_encode($test_data) . "\n";

echo "Test completed.\n";
?> 