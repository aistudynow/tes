<?php
require __DIR__ . '/db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Valid id required']);
  exit;
}

$stmt = $conn->prepare("DELETE FROM templates WHERE id = ?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['error' => 'Prepare failed']);
  exit;
}

$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['error' => 'Delete failed']);
  exit;
}

echo json_encode(['success' => true]);
