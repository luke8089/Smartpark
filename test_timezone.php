<?php
// Test script to verify timezone handling
echo "=== Timezone Test for SmartPark Password Reset ===\n\n";

// Set timezone to UTC
date_default_timezone_set('UTC');

echo "Current UTC time: " . gmdate('Y-m-d H:i:s T') . "\n";
echo "Current UTC timestamp: " . gmdate('U') . "\n\n";

// Test token expiration calculation
$token_expires = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
echo "Token expires at (UTC): " . $token_expires . "\n";
echo "Token expires timestamp: " . strtotime($token_expires) . "\n\n";

// Test different timezone scenarios
$timezones = ['UTC', 'America/New_York', 'Europe/London', 'Asia/Tokyo', 'Africa/Nairobi'];

foreach ($timezones as $tz) {
    echo "Testing timezone: $tz\n";
    date_default_timezone_set($tz);
    
    $local_time = date('Y-m-d H:i:s T');
    $utc_time = gmdate('Y-m-d H:i:s T');
    $local_timestamp = time();
    $utc_timestamp = gmdate('U');
    
    echo "  Local time: $local_time\n";
    echo "  UTC time: $utc_time\n";
    echo "  Local timestamp: $local_timestamp\n";
    echo "  UTC timestamp: $utc_timestamp\n";
    echo "  Difference: " . ($local_timestamp - $utc_timestamp) . " seconds\n\n";
}

// Reset to UTC
date_default_timezone_set('UTC');
echo "Reset to UTC: " . gmdate('Y-m-d H:i:s T') . "\n";

echo "\n=== Test Complete ===\n";
echo "The password reset system uses UTC timestamps for universal compatibility.\n";
echo "All expiration checks use gmdate('U') to ensure consistent time comparison.\n";
?> 