<?php
// Direct connection test - view this in your browser
header('Content-Type: text/html; charset=utf-8');

$DB_USER = 'promptbuilder';
$DB_PASS = '6rs54EJXuGDu088rADLh';
$DB_NAME = 'promptbuilder';

echo "<!DOCTYPE html>
<html>
<head>
  <title>Database Connection Test</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { color: green; background: #e8f5e9; padding: 10px; margin: 10px 0; border-left: 4px solid green; }
    .error { color: red; background: #ffebee; padding: 10px; margin: 10px 0; border-left: 4px solid red; }
    .info { color: blue; background: #e3f2fd; padding: 10px; margin: 10px 0; border-left: 4px solid blue; }
    h1 { color: #333; }
    pre { background: #fff; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
  </style>
</head>
<body>
  <h1>Database Connection Test</h1>
";

// Check if mysqli extension is loaded
if (!extension_loaded('mysqli')) {
  echo "<div class='error'>ERROR: mysqli extension is not loaded!</div>";
  echo "<div class='info'>Contact your hosting provider to enable mysqli extension.</div>";
  exit;
}

echo "<div class='success'>mysqli extension is loaded</div>";

// Test different connection methods
$hosts_to_test = [
  ['127.0.0.1', null, 'TCP to 127.0.0.1'],
  ['localhost', null, 'localhost'],
  ['localhost', '/var/run/mysqld/mysqld.sock', 'Unix Socket /var/run/mysqld/mysqld.sock'],
  ['localhost', '/tmp/mysql.sock', 'Unix Socket /tmp/mysql.sock'],
];

$successful_connection = null;

echo "<h2>Testing Connections:</h2>";

foreach ($hosts_to_test as [$host, $socket, $desc]) {
  echo "<h3>Testing: $desc</h3>";

  mysqli_report(MYSQLI_REPORT_OFF);
  $conn = @new mysqli($host, $DB_USER, $DB_PASS, $DB_NAME, 3306, $socket);

  if ($conn && !$conn->connect_errno) {
    echo "<div class='success'>✓ Connection successful!</div>";
    echo "<pre>Host: $host\n";
    if ($socket) echo "Socket: $socket\n";
    echo "MySQL Version: " . $conn->server_info . "\n";
    echo "Connection ID: " . $conn->thread_id . "</pre>";

    if (!$successful_connection) {
      $successful_connection = $conn;

      // Test table creation
      echo "<h3>Testing Table Operations:</h3>";
      $result = $conn->query("
        CREATE TABLE IF NOT EXISTS templates (
          id INT AUTO_INCREMENT PRIMARY KEY,
          title VARCHAR(255) NOT NULL,
          content LONGTEXT NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
      ");

      if ($result) {
        echo "<div class='success'>✓ Templates table verified/created successfully</div>";

        // Check if table has records
        $count_result = $conn->query("SELECT COUNT(*) as count FROM templates");
        if ($count_result) {
          $row = $count_result->fetch_assoc();
          echo "<div class='info'>Current templates in database: " . $row['count'] . "</div>";
        }
      } else {
        echo "<div class='error'>✗ Failed to create/verify templates table: " . $conn->error . "</div>";
      }
    }

    break;
  } else {
    $error = $conn ? $conn->connect_error : 'Connection object creation failed';
    $errno = $conn ? $conn->connect_errno : 'N/A';
    echo "<div class='error'>✗ Connection failed<br>Error ($errno): $error</div>";
  }
}

if (!$successful_connection) {
  echo "<h2 class='error'>No successful connection found!</h2>";
  echo "<div class='info'>
    <strong>Troubleshooting Steps:</strong>
    <ol>
      <li>Verify MySQL is running on your server</li>
      <li>Check that database 'promptbuilder' exists</li>
      <li>Verify user 'promptbuilder' has correct permissions</li>
      <li>Check the password is correct</li>
      <li>In CloudPanel, go to Databases and verify the database and user settings</li>
    </ol>
  </div>";
} else {
  echo "<h2 class='success'>✓ All Tests Passed!</h2>";
  echo "<div class='info'>Your database connection is working correctly. If your app still shows errors, check:
    <ul>
      <li>File permissions (PHP files should be readable)</li>
      <li>Web server configuration</li>
      <li>Browser console for JavaScript errors</li>
    </ul>
  </div>";
}

echo "</body></html>";
