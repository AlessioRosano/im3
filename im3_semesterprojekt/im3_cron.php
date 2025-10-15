<?php
declare(strict_types=1);

// --- Logging ---
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/im3_cron.log');
error_reporting(E_ALL);

set_error_handler(function($s, $m, $f, $l) {
  error_log("PHP[$s] $m in $f:$l");
});
set_exception_handler(function($e) {
  error_log("Uncaught: " . $e);
  http_response_code(500);
  header('Content-Type: text/plain; charset=utf-8');
  echo "error\n";
  exit;
});

// --- Config laden (einzige Quelle) ---
require __DIR__ . '/im3_config.php';

// Optional: schnelle DB-Diagnose beim Cron
try {
  $pdo = db(); // wirft Exception bei falschen Credentials
  error_log("CRON: DB ok as " . ($pdo->query("SELECT USER()")->fetchColumn() ?: 'n/a'));
} catch (Throwable $e) {
  error_log("CRON: DB ERROR " . $e->getMessage());
  http_response_code(500);
  header('Content-Type: text/plain; charset=utf-8');
  echo "DB-Fehler: " . $e->getMessage() . "\n";
  exit;
}

// --- Secret prüfen ---
$secret = 'dasistunsergeheimertokenhihi'; // belass dein Secret hier
if (!isset($_GET['key']) || !hash_equals($secret, (string)$_GET['key'])) {
  http_response_code(403);
  header('Content-Type: text/plain; charset=utf-8');
  echo "Forbidden\n";
  exit;
}

// --- Job starten ---
header('Content-Type: text/plain; charset=utf-8');
try {
  // Dein Extract/Sync
  include __DIR__ . '/im3_extract.php';
  echo "OK\n";
} catch (Throwable $e) {
  error_log("CRON: EXTRACT ERROR " . $e->getMessage());
  http_response_code(500);
  echo "CRON-Fehler: " . $e->getMessage() . "\n";
}