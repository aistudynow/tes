<?php
require __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid id']);
  exit;
}

$q = "SELECT id, title, content, created_at FROM templates WHERE id = $id LIMIT 1";
$res = $conn->query($q);
if (!$res || $res->num_rows === 0) {
  http_response_code(404);
  echo json_encode(['error' => 'Template not found']);
  exit;
}

echo json_encode($res->fetch_assoc(), JSON_UNESCAPED_UNICODE);
