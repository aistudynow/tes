<?php
require __DIR__ . '/db.php';

$res = $conn->query("SELECT id, title FROM templates ORDER BY created_at DESC");
if (!$res) {
  http_response_code(500);
  echo json_encode(['error' => 'Query failed']);
  exit;
}

$out = [];
while ($row = $res->fetch_assoc()) $out[] = $row;

echo json_encode($out, JSON_UNESCAPED_UNICODE);
