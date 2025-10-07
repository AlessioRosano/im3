<?php
declare(strict_types=1);

// im3_extract.php – Lazy-Sync in DB + Rückgabe der letzten 100 Zeilen

require __DIR__ . '/im3_config.php';
$pdo = new PDO($dsn, $username, $password, $options);

// --- Settings ---
const LIMIT_PER_PAGE    = 100;   // Opendatasoft v2.1 max 100
const MAX_BATCHES_SYNC  = 6;     // pro Aufruf max. ~600 Datensätze (schont Rate-Limit)
const OFFSET_CAP        = 9800;  // Sicherheit für initialen Backfill
const MAX_RETRIES_429   = 3;
const RETRY_BACKOFF_SEC = 3;

// --- Upsert (ohne 'summe' – wird in DB generiert) ---
$upsert = $pdo->prepare("
  INSERT INTO fussgaenger_vadianstrasse
    (device_id, measured_at_new, datum_tag, data_right, data_left)
  VALUES
    (:device_id, :measured_at_new, :datum_tag, :data_right, :data_left)
  ON DUPLICATE KEY UPDATE
    datum_tag = VALUES(datum_tag),
    data_right = VALUES(data_right),
    data_left  = VALUES(data_left),
    updated_at = CURRENT_TIMESTAMP
");

// --- letzter gespeicherter Zeitpunkt (UTC) ---
$last = $pdo->query("SELECT MAX(measured_at_new) AS mx FROM fussgaenger_vadianstrasse")->fetch()['mx'] ?? null;

// --- WHERE für inkrementelles Nachladen ---
$where = null;
if ($last) {
  $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $last, new DateTimeZone('UTC'));
  if ($dt) $where = 'measured_at_new > "' . $dt->format('Y-m-d\TH:i:s\Z') . '"';
}

// --- API-Page-Fetcher mit 429-Backoff ---
function fetchPage(int $offset, ?string $where, int $try = 0): array {
  $base = 'https://daten.stadt.sg.ch/api/explore/v2.1/catalog/datasets/fussganger-stgaller-innenstadt-vadianstrasse/records';
  $params = [
    'order_by' => 'measured_at_new ASC',
    'limit'    => LIMIT_PER_PAGE,
    'offset'   => $offset,
  ];
  if ($where) $params['where'] = $where;

  $url = $base . '?' . http_build_query($params);
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT        => 25,
    CURLOPT_HTTPHEADER     => ['Accept: application/json', 'User-Agent: im3-extract/1.0'],
  ]);
  $res = curl_exec($ch);
  if ($res === false) { $e = curl_error($ch); curl_close($ch); throw new RuntimeException('cURL: '.$e); }
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($code === 429) {
    if ($try >= MAX_RETRIES_429) return []; // freundlich abbrechen
    sleep(RETRY_BACKOFF_SEC * max(1, $try + 1));
    return fetchPage($offset, $where, $try + 1);
  }
  if ($code < 200 || $code >= 300) {
    throw new RuntimeException('HTTP '.$code);
  }

  $json = json_decode($res, true);
  return $json['results'] ?? [];
}

// --- Lazy-Sync ---
$offset  = 0;
$batches = 0;

try {
  if ($where) {
    // Inkrementell: nur neue Datensätze seit letztem DB-Zeitpunkt
    while ($batches < MAX_BATCHES_SYNC) {
      $batch = fetchPage($offset, $where);
      if (!$batch) break;

      $lastSeenIso = null;

      foreach ($batch as $r) {
        $iso      = $r['measured_at_new'] ?? null;
        $datumTag = $r['datum_tag'] ?? null;
        if (!$iso || !$datumTag) continue;

        $right = (int)($r['data_right'] ?? 0);
        $left  = (int)($r['data_left']  ?? 0);

        // ISO → MySQL DATETIME (UTC)
        $mysqlUtc = (new DateTimeImmutable($iso))
                      ->setTimezone(new DateTimeZone('UTC'))
                      ->format('Y-m-d H:i:s');

        $upsert->execute([
          ':device_id'       => $r['device_id'] ?? null,
          ':measured_at_new' => $mysqlUtc,
          ':datum_tag'       => $datumTag,
          ':data_right'      => $right,
          ':data_left'       => $left,
        ]);

        $lastSeenIso = $iso;
      }

      // Fortschritt hochziehen
      if ($lastSeenIso) {
        $lastUtcIso = (new DateTimeImmutable($lastSeenIso))
                        ->setTimezone(new DateTimeZone('UTC'))
                        ->format('Y-m-d\TH:i:s\Z');
        $where  = 'measured_at_new > "' . $lastUtcIso . '"';
        $offset = 0;
      } else {
        $offset += LIMIT_PER_PAGE;
      }

      $batches++;
    }

  } else {
    // Erstbefüllung: Voll-Backfill (ohne WHERE, aber gedeckelt)
    while ($batches < MAX_BATCHES_SYNC && $offset <= OFFSET_CAP) {
      $batch = fetchPage($offset, null);
      if (!$batch) break;

      foreach ($batch as $r) {
        $iso      = $r['measured_at_new'] ?? null;
        $datumTag = $r['datum_tag'] ?? null;
        if (!$iso || !$datumTag) continue;

        $right = (int)($r['data_right'] ?? 0);
        $left  = (int)($r['data_left']  ?? 0);

        $mysqlUtc = (new DateTimeImmutable($iso))
                      ->setTimezone(new DateTimeZone('UTC'))
                      ->format('Y-m-d H:i:s');

        $upsert->execute([
          ':device_id'       => $r['device_id'] ?? null,
          ':measured_at_new' => $mysqlUtc,
          ':datum_tag'       => $datumTag,
          ':data_right'      => $right,
          ':data_left'       => $left,
        ]);
      }

      $offset  += LIMIT_PER_PAGE;
      $batches++;
    }
  }

} catch (Throwable $e) {
  // weich: wir liefern unten die DB-Daten, auch wenn API gerade zickt
  // (429 wird oben ohnehin abgefedert)
}

// --- Daten aus DB lesen und zurückgeben ---
$stmt = $pdo->prepare("
  SELECT device_id, measured_at_new, datum_tag, data_right, data_left, summe
  FROM fussgaenger_vadianstrasse
  ORDER BY measured_at_new DESC
  LIMIT 100
");
$stmt->execute();
$rows = $stmt->fetchAll();

return $rows; // nur return – kein echo