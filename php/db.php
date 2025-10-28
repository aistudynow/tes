<?php
// Always JSON for API endpoints
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Your DB credentials
$DB_USER = 'aistudy2';
$DB_PASS = '2oD9qRs2hlfaoKsbgDH4';
$DB_NAME = 'aistudy2';

// Try multiple connection strategies with detailed error tracking
function connect_db($host, $user, $pass, $db, $port = 3306, $socket = null) {
  mysqli_report(MYSQLI_REPORT_OFF);
  $conn = @new mysqli($host, $user, $pass, $db, $port, $socket);
  if ($conn && !$conn->connect_errno) {
    $conn->set_charset('utf8mb4');
    return $conn;
  }
  return null;
}

$conn = null;
$connection_attempts = [];

// 1) Try TCP 127.0.0.1
$conn = connect_db('127.0.0.1', $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
  $connection_attempts[] = '127.0.0.1:3306';

  // 2) Try localhost (often uses the socket)
  $conn = connect_db('localhost', $DB_USER, $DB_PASS, $DB_NAME);
  if (!$conn) {
    $connection_attempts[] = 'localhost:3306';

    // 3) Try common Unix socket path
    $conn = connect_db('localhost', $DB_USER, $DB_PASS, $DB_NAME, 3306, '/var/run/mysqld/mysqld.sock');
    if (!$conn) {
      $connection_attempts[] = 'socket:/var/run/mysqld/mysqld.sock';

      // 4) Try alternative socket path
      $conn = connect_db('localhost', $DB_USER, $DB_PASS, $DB_NAME, 3306, '/tmp/mysql.sock');
      if (!$conn) {
        $connection_attempts[] = 'socket:/tmp/mysql.sock';
      }
    }
  }
}

if (!$conn) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Database connection failed',
    'details' => 'Could not connect to MySQL server',
    'attempts' => $connection_attempts,
    'user' => $DB_USER,
    'database' => $DB_NAME,
    'hint' => 'Check that MySQL is running and credentials are correct'
  ]);
  exit;
}

// Ensure table exists (safe if already exists)
$createTableResult = $conn->query("
  CREATE TABLE IF NOT EXISTS templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

if (!$createTableResult) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Failed to create/verify templates table',
    'mysql_error' => $conn->error
  ]);
  exit;
}
