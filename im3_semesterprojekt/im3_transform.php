<?php
declare(strict_types=1);

// formt DB-Rohdaten zu JSON fürs Frontend
$data = include __DIR__ . '/im3_extract.php';

if (!is_array($data)) {
  throw new Exception("Unerwartetes Format aus im3_extract.php"); // aber: leeres Array ist ok
}

$transformed = [];
$tzZurich = new DateTimeZone('Europe/Zurich');

foreach ($data as $row) {
  $measuredUtc = $row['measured_at_new'] ?? null; // MySQL DATETIME (UTC)
  $datum_tag   = $row['datum_tag'] ?? null;

  $right = isset($row['data_right']) ? (int)$row['data_right'] : 0;
  $left  = isset($row['data_left'])  ? (int)$row['data_left']  : 0;
  $summe = isset($row['summe'])      ? (int)$row['summe']      : ($right + $left);

  $formatted = null;
  if ($measuredUtc) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $measuredUtc, new DateTimeZone('UTC'));
    if ($dt) $formatted = $dt->setTimezone($tzZurich)->format('Y-m-d H:i:s');
  }

  $transformed[] = [
    'measured_at_new' => $formatted,
    'datum_tag'       => $datum_tag,
    'data_right'      => $right,
    'data_left'       => $left,
    'summe'           => $summe,
  ];
}

$json = json_encode($transformed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json === false) {
  throw new Exception('JSON-Fehler: ' . json_last_error_msg());
}

return $json; // return, nicht echo