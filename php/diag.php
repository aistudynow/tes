<?php
header('Content-Type: application/json; charset=utf-8');
$u = 'promptbuilder';
$p = '6rs54EJXuGDu088rADLh';
$d = 'promptbuilder';

$out = [
  'php_version' => PHP_VERSION,
  'sapi' => php_sapi_name(),
  'cwd' => getcwd(),
  'mysqli_loaded' => extension_loaded('mysqli'),
  'pdo_mysql_loaded' => extension_loaded('pdo_mysql'),
  'tests' => []
];

// Try PDO first if available
if (extension_loaded('pdo_mysql')) {
  $dsn = 'mysql:host=127.0.0.1;dbname='.$d.';charset=utf8mb4';
  try {
    $t0 = microtime(true);
    $pdo = new PDO($dsn, $u, $p, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $ok = $pdo->query('SELECT 1')->fetchColumn() == 1;
    $out['tests'][] = [
      'driver' => 'pdo_mysql',
      'host' => '127.0.0.1',
      'ok' => $ok,
      'duration_ms' => round((microtime(true) - $t0) * 1000)
    ];
  } catch (Throwable $e) {
    $out['tests'][] = [
      'driver' => 'pdo_mysql',
      'host' => '127.0.0.1',
      'ok' => false,
      'error' => $e->getMessage()
    ];
  }
}

// Only attempt mysqli tests if the extension exists
if (extension_loaded('mysqli')) {
  $hosts = [
    ['127.0.0.1', null],
    ['localhost', null],
    ['localhost', '/var/run/mysqld/mysqld.sock']
  ];
  foreach ($hosts as [$h, $sock]) {
    $t0 = microtime(true);
    try {
      $conn = @new mysqli($h, $u, $p, $d, 3306, $sock);
      if ($conn && !$conn->connect_errno) {
        $conn->set_charset('utf8mb4');
        $ok = $conn->query('SELECT 1') ? true : false;
        $out['tests'][] = [
          'driver' => 'mysqli',
          'host' => $h . ($sock ? " socket:$sock" : ""),
          'ok' => $ok,
          'duration_ms' => round((microtime(true) - $t0) * 1000)
        ];
      } else {
        $out['tests'][] = [
          'driver' => 'mysqli',
          'host' => $h . ($sock ? " socket:$sock" : ""),
          'ok' => false,
          'error' => $conn ? $conn->connect_error : 'construct failed'
        ];
      }
    } catch (Throwable $e) {
      $out['tests'][] = [
        'driver' => 'mysqli',
        'host' => $h . ($sock ? " socket:$sock" : ""),
        'ok' => false,
        'error' => $e->getMessage()
      ];
    }
  }
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
