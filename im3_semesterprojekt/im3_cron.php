<?php
declare(strict_types=1);

/**
 * im3_cron.php
 * Sicherer Cron-Endpoint, der den Lazy-Sync anstößt
 * (via include von im3_extract.php) und eine kurze Text-Antwort liefert.
 *
 * HINWEIS:
 * - Diese Datei NICHT ins Repo committen (in .gitignore aufnehmen).
 * - Secret unten setzen und in der Cron-URL als ?key=... übergeben.
 */

// -------- 1) Secret prüfen --------
$secret = 'dasistunsergeheimertokenhihi'; // <-- hier DEIN langen, zufälligen Token setzen
if (!isset($_GET['key']) || !hash_equals($secret, (string)$_GET['key'])) {
  http_response_code(403);
  header('Content-Type: text/plain; charset=utf-8');
  echo "Forbidden\n";
  exit;
}

// -------- 2) Sync anstoßen --------
// Wichtig: im3_extract.php führt den API->DB Lazy-Sync aus
// und gibt am Ende die letzten Zeilen aus der DB zurück.
// Wir ignorieren die Rückgabe – uns reicht, dass der Sync läuft.
header('Content-Type: text/plain; charset=utf-8');

try {
  include __DIR__ . '/im3_extract.php';
  echo "OK\n";
} catch (Throwable $e) {
  http_response_code(500);
  echo "CRON-Fehler: " . $e->getMessage() . "\n";
}