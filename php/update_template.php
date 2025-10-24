<?php
require __DIR__ . '/db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($id <= 0 || $content === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Valid id and content are required']);
  exit;
}

$stmt = $conn->prepare("UPDATE templates SET content = ? WHERE id = ?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['error' => 'Prepare failed']);
  exit;
}

$stmt->bind_param("si", $content, $id);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['error' => 'Update failed']);
  exit;
}

echo json_encode(['success' => true]);
