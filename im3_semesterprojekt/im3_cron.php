<?php
declare(strict_types=1);

// ---- Secret prüfen (URL: ?key=DEIN_GEHEIMES_TOKEN) -------------------------
$secret = 'deinLangerZufallsTokenVonOben'; // <- setze hier DEIN geheimes Token
if (!isset($_GET['key']) || !hash_equals($secret, (string)$_GET['key'])) {
  http_response_code(403);
  header('Content-Type: text/plain; charset=utf-8');
  echo "Forbidden\n";
  exit;
}

// ---- Sync anstossen ---------------------------------------------------------
header('Content-Type: text/plain; charset=utf-8');
try {
  include __DIR__ . '/im3_extract.php';
  echo "OK\n";
} catch (Throwable $e) {
  http_response_code(500);
  echo "CRON-Fehler: " . $e->getMessage() . "\n";
}