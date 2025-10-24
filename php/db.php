<?php
// Always JSON for API endpoints
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Your DB credentials
$DB_USER = 'promptbuilder';
$DB_PASS = '6rs54EJXuGDu088rADLh';
$DB_NAME = 'promptbuilder';

// Try multiple connection strategies: TCP 127.0.0.1 -> localhost socket
function connect_db($host, $user, $pass, $db, $port = 3306, $socket = null) {
  $conn = @new mysqli($host, $user, $pass, $db, $port, $socket);
  if ($conn && !$conn->connect_errno) {
    $conn->set_charset('utf8mb4');
    return $conn;
  }
  return null;
}

$conn = null;

// 1) Try TCP
$conn = connect_db('127.0.0.1', $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
  // 2) Try localhost (often uses the socket)
  $conn = connect_db('localhost', $DB_USER, $DB_PASS, $DB_NAME);
}
if (!$conn) {
  // 3) Try common Unix socket path
  $conn = connect_db('localhost', $DB_USER, $DB_PASS, $DB_NAME, 3306, '/var/run/mysqld/mysqld.sock');
}

if (!$conn) {
  http_response_code(500);
  echo json_encode(['error' => 'DB connection failed']);
  exit;
}

// Ensure table exists (safe if already exists)
$conn->query("
  CREATE TABLE IF NOT EXISTS templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
