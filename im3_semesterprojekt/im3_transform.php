<?php
declare(strict_types=1);

// Converts raw DB rows to a clean JSON payload.
$data = include __DIR__ . '/im3_extract.php';

if (!is_array($data)) {
  throw new Exception('Keine gültigen Daten aus im3_extract.php erhalten.');
}

$tzZurich = new DateTimeZone('Europe/Zurich');
$out = [];

foreach ($data as $row) {
  $utc      = $row['measured_at_new'] ?? null; // 'Y-m-d H:i:s' UTC in DB
  $datumTag = $row['datum_tag']       ?? null;

  $right = (int)($row['data_right'] ?? 0);
  $left  = (int)($row['data_left']  ?? 0);
  $summe = (int)($row['summe']      ?? ($right + $left));

  $local = null;
  if ($utc) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $utc, new DateTimeZone('UTC'));
    if ($dt) {
      $local = $dt->setTimezone($tzZurich)->format('Y-m-d H:i:s');
    }
  }

  $out[] = [
    'measured_at_new' => $local,   // Europe/Zurich
    'datum_tag'       => $datumTag,
    'data_right'      => $right,
    'data_left'       => $left,
    'summe'           => $summe,
  ];
}

$json = json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
if ($json === false) {
  throw new Exception('JSON-Fehler: ' . json_last_error_msg());
}
return $json;