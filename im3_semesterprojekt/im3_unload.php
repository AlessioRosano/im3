<?php
declare(strict_types=1);

// --- CORS / JSON ---
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

try {
  // DB verbinden
  require __DIR__ . '/im3_config.php';
  $pdo = new PDO($dsn, $username, $password, $options);

  // --- Eingaben mit Defaults (heute in Europe/Zurich) ---
  $tzZurich = new DateTimeZone('Europe/Zurich');
  $nowZ  = new DateTimeImmutable('now', $tzZurich);
  $start = $nowZ->setTime(0,0,0);
  $end   = $start->modify('+1 day');

  $fromParam = $_GET['from'] ?? $start->format('Y-m-d'); // inklusiv
  $toParam   = $_GET['to']   ?? $end->format('Y-m-d');   // exklusiv

  $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5000; // <-- KEINE Konstante
  if ($limit < 1) $limit = 1;
  if ($limit > 10000) $limit = 10000;

  $deviceId = isset($_GET['device_id']) && $_GET['device_id'] !== '' ? (string)$_GET['device_id'] : null;

  // In UTC für die DB (deine DB speichert measured_at_new in UTC)
  $fromUtc = (new DateTimeImmutable($fromParam, $tzZurich))->setTime(0,0)->setTimezone(new DateTimeZone('UTC'));
  $toUtc   = (new DateTimeImmutable($toParam,   $tzZurich))->setTime(0,0)->setTimezone(new DateTimeZone('UTC'));

  // --- SQL bauen ---
  $sql = "
    SELECT device_id, measured_at_new, datum_tag, data_right, data_left, summe
    FROM fussgaenger_vadianstrasse
    WHERE measured_at_new >= :from_utc AND measured_at_new < :to_utc
  ";
  $params = [
    ':from_utc' => $fromUtc->format('Y-m-d H:i:s'),
    ':to_utc'   => $toUtc->format('Y-m-d H:i:s'),
  ];

  if ($deviceId) {
    $sql .= " AND device_id = :device_id";
    $params[':device_id'] = $deviceId;
  }

  $sql .= " ORDER BY measured_at_new ASC LIMIT {$limit}";

  $st = $pdo->prepare($sql);
  $st->execute($params);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if (json_last_error() !== JSON_ERROR_NONE) {
    throw new RuntimeException('JSON error: ' . json_last_error_msg());
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}