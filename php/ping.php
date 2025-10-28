<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
  'ok' => true,
  'php_version' => PHP_VERSION,
  'sapi' => php_sapi_name(),
  'cwd' => getcwd(),
  'mysqli_available' => extension_loaded('mysqli'),
  'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown'
]);
