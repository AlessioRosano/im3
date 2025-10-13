<?php
declare(strict_types=1);

/**
 * im3_unload.php – API/Endpoint für Ausgaben aus der DB.
 * - Standard: JSON (maschinenlesbar), mit CORS via .htaccess
 * - Optional: ?format=html gibt eine schlichte HTML-Tabelle zurück (wie load.php)
 * - Parameter:
 *     - format=html|json (default=json)
 *     - tz=Europe/Zurich|UTC|... (default=Europe/Zurich)
 *     - limit=1..500 (default=200)
 *     - pretty=0|1 (default=0) – nur für json
 */

require __DIR__ . '/im3_config.php';

$pdo = new PDO($dsn, $username, $password, $options);

// --- Query-Parameter lesen ---
$format = isset($_GET['format']) ? strtolower((string)$_GET['format']) : 'json';
$limit  = isset($_GET['limit'])  ? max(1, min(500, (int)$_GET['limit'])) : 200;
$tzName = isset($_GET['tz'])     ? (string)$_GET['tz'] : 'Europe/Zurich';
$pretty = isset($_GET['pretty']) ? ((int)$_GET['pretty'] === 1) : false;

// Timezone vorbereiten (Fallback auf UTC, falls ungültig)
try {
  $tz = new DateTimeZone($tzName);
} catch (Throwable $e) {
  $tzName = 'UTC';
  $tz = new DateTimeZone('UTC');
}

$stmt = $pdo->prepare("
  SELECT device_id, measured_at_new, datum_tag, data_right, data_left, summe
  FROM fussgaenger_vadianstrasse
  ORDER BY measured_at_new DESC
  LIMIT :lim
");
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();

$rows = $stmt->fetchAll();

// UTC (DB) -> gewünschte Zeitzone für Ausgabe angleichen:
$out = [];
foreach ($rows as $r) {
  $utcStr = $r['measured_at_new'] ?? null; // 'Y-m-d H:i:s' in UTC
  $formatted = null;
  if ($utcStr) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $utcStr, new DateTimeZone('UTC'));
    if ($dt) {
      $formatted = $dt->setTimezone($tz)->format('Y-m-d H:i:s');
    }
  }

  $right = isset($r['data_right']) ? (int)$r['data_right'] : 0;
  $left  = isset($r['data_left'])  ? (int)$r['data_left']  : 0;
  $summe = isset($r['summe'])      ? (int)$r['summe']      : ($right + $left);

  $out[] = [
    'measured_at_new' => $formatted,     // jetzt im gewünschten TZ
    'datum_tag'       => $r['datum_tag'] ?? null,
    'data_right'      => $right,
    'data_left'       => $left,
    'summe'           => $summe,
    'device_id'       => $r['device_id'] ?? null,
  ];
}

if ($format === 'html') {
  // --- HTML-Ausgabe (ähnlich load.php) ---
  header('Content-Type: text/html; charset=utf-8');
  ?>
  <!doctype html>
  <html lang="de">
  <head>
    <meta charset="utf-8">
    <title>API-Ansicht (HTML) – Fussgänger-Zählungen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      :root { --pad:.6rem; --border:#e5e7eb; --bg:#fafafa; --muted:#6b7280; }
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 0; color: #111827; }
      .wrap { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
      h1 { margin: 0 0 .25rem; font-size: 1.4rem; }
      .meta { color: var(--muted); margin: 0 0 1rem; }
      table { width: 100%; border-collapse: collapse; border: 1px solid var(--border); background: #fff; }
      thead th { text-align: left; font-weight: 600; border-bottom: 1px solid var(--border); padding: var(--pad); background: var(--bg); }
      tbody td { padding: var(--pad); border-bottom: 1px solid var(--border); vertical-align: top; }
      tbody tr:hover { background: #fcfcfd; }
      .num { text-align: right; font-variant-numeric: tabular-nums; }
      .tag { display:inline-block; font-size:.8rem; padding:.1rem .4rem; border:1px solid var(--border); border-radius:.375rem; color:#374151; background:#fff; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <h1>Fussgänger-Zählungen (HTML-Ansicht aus API)</h1>
      <p class="meta">
        Einträge: <?= number_format(count($out), 0, ',', '\'') ?> · TZ: <?= htmlspecialchars($tzName) ?> · Limit: <?= (int)$limit ?>
      </p>
      <table>
        <thead>
          <tr>
            <th>Zeit (<?= htmlspecialchars($tzName) ?>)</th>
            <th>Datum</th>
            <th class="num">Rechts</th>
            <th class="num">Links</th>
            <th class="num">Summe</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($out as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['measured_at_new'] ?? '') ?></td>
            <td><span class="tag"><?= htmlspecialchars($row['datum_tag'] ?? '') ?></span></td>
            <td class="num"><?= (int)($row['data_right'] ?? 0) ?></td>
            <td class="num"><?= (int)($row['data_left'] ?? 0) ?></td>
            <td class="num"><?= (int)($row['summe'] ?? 0) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// --- JSON (Default) ---
header('Content-Type: application/json; charset=utf-8');
$options = 0;
if ($pretty) $options |= JSON_PRETTY_PRINT;
echo json_encode([
  'meta' => [
    'timezone' => $tzName,
    'limit'    => $limit,
    'count'    => count($out),
    'ordered'  => 'DESC measured_at_new',
  ],
  'data' => $out,
], $options | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);