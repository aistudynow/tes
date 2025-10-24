<?php
require __DIR__ . '/db.php';

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($title === '' || $content === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Title and content are required']);
  exit;
}

$stmt = $conn->prepare("INSERT INTO templates (title, content) VALUES (?, ?)");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['error' => 'Prepare failed']);
  exit;
}

$stmt->bind_param("ss", $title, $content);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['error' => 'Insert failed']);
  exit;
}

echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
