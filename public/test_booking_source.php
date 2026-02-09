<?php
/**
 * Direct test of the booking page to check if scripts are loaded
 */

// Set proper headers for HTML
header('Content-Type: text/html; charset=UTF-8');

?><!DOCTYPE html>
<html>
<head>
    <title>Booking Page Source Checker</title>
</head>
<body>
<h1>Checking Booking Page Source</h1>

<p><strong>Status:</strong> Fetching page from http://127.0.0.1:8000/book...</p>

<?php
$pageContent = @file_get_contents('http://127.0.0.1:8000/book');

if ($pageContent === false) {
    echo "<p style='color: red;'><strong>❌ FAILED:</strong> Could not fetch page. Is the server running?</p>";
    echo "<p>Try: <code>php artisan serve --port=8000</code></p>";
    exit;
}

echo "<p style='color: green;'><strong>✅ Page fetched successfully!</strong></p>";

// Check for key elements
$checks = [
    'booking-slots.js included' => preg_match('/["\']js\/booking-slots\.js["\']/', $pageContent) > 0,
    'slots-grid div exists' => strpos($pageContent, 'id="slots-grid"') !== false,
    'appointment_date input' => strpos($pageContent, 'id="appointment_date"') !== false,
    'appointment_time input' => strpos($pageContent, 'id="appointment_time"') !== false,
    'BookingSlotManager init' => preg_match('/new\s+BookingSlotManager/', $pageContent) > 0,
    'handleDateChange defined' => strpos($pageContent, 'function handleDateChange') !== false,
];

echo "<h2>Page Structure Checks:</h2>";
echo "<ul>";
foreach ($checks as $check => $passed) {
    $status = $passed ? '✅ PASS' : '❌ FAIL';
    echo "<li><strong>$status</strong> - $check</li>";
}
echo "</ul>";

// Show relevant parts
echo "<h2>Relevant Page Content:</h2>";

// Extract script section
if (preg_match('/<script[^>]*>.*?new\s+BookingSlotManager.*?<\/script>/s', $pageContent, $matches)) {
    echo "<h3>Initialization Script:</h3>";
    echo "<pre><code>" . htmlspecialchars($matches[0]) . "</code></pre>";
} else {
    echo "<p style='color: orange;'><strong>⚠️</strong> Could not find BookingSlotManager initialization</p>";
}

// Check asset loading
echo "<h3>Script Tag for booking-slots.js:</h3>";
if (preg_match('/<script[^>]*booking-slots\.js[^>]*><\/script>/i', $pageContent, $matches)) {
    echo "<pre><code>" . htmlspecialchars($matches[0]) . "</code></pre>";
} else {
    echo "<p style='color: red;'><strong>❌</strong> Script tag for booking-slots.js not found</p>";
}
?>

<hr>
<p><strong>Next steps:</strong></p>
<ol>
    <li>If all checks pass: Open the browser to http://127.0.0.1:8000/book</li>
    <li>Press F12 to open Developer Tools</li>
    <li>Go to Console tab</li>
    <li>Select a service and pick a date</li>
    <li>Check if slots appear and if there are any errors</li>
</ol>

</body>
</html>
