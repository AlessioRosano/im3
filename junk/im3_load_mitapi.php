<?php
// LOAD: zeigt HTML-Tabelle, sauberer Fehlerfall
try {
    $jsonData  = include __DIR__ . '/im3_transform.php';
    $dataArray = json_decode($jsonData, true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(502);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Daten konnten nicht geladen werden:\n" . $e->getMessage();
    exit;
}

// Sortieren (neueste zuerst)
usort($dataArray, function ($a, $b) {
    $ta = $a['measured_at_new'] ?? '';
    $tb = $b['measured_at_new'] ?? '';
    return strcmp($tb, $ta); // 'Y-m-d H:i:s' sortiert lexikografisch korrekt
});

// Optional limitieren
$maxRows = 200;
if (count($dataArray) > $maxRows) {
    $dataArray = array_slice($dataArray, 0, $maxRows);
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Fussgänger-Zählungen – Übersicht</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --pad: .6rem; --border: #e5e7eb; --bg: #fafafa; --muted: #6b7280; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 0; background: white; color: #111827; }
    .wrap { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
    h1 { margin: 0 0 0.25rem; font-size: 1.4rem; }
    .meta { color: var(--muted); margin: 0 0 1rem; }
    table { width: 100%; border-collapse: collapse; background: white; border: 1px solid var(--border); }
    thead th { text-align: left; font-weight: 600; border-bottom: 1px solid var(--border); padding: var(--pad); background: var(--bg); }
    tbody td { padding: var(--pad); border-bottom: 1px solid var(--border); vertical-align: top; }
    tbody tr:hover { background: #fcfcfd; }
    .num { text-align: right; font-variant-numeric: tabular-nums; }
    .tag { display: inline-block; font-size: .8rem; padding: .1rem .4rem; border: 1px solid var(--border); border-radius: .375rem; color: #374151; background: #fff; }
    .footer { margin-top: .75rem; color: var(--muted); font-size: .9rem; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Fussgänger-Zählungen</h1>
    <p class="meta">
      Einträge: <?= number_format(count($dataArray), 0, ',', '\'') ?> ·
      Zeitraum (max. <?= (int)$maxRows ?>, neueste zuerst)
    </p>

    <table>
      <thead>
        <tr>
          <th>Zeit (Europe/Zurich)</th>
          <th>Datum</th>
          <th class="num">Rechts</th>
          <th class="num">Links</th>
          <th class="num">Summe</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dataArray as $row): ?>
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

    <p class="footer">
      Quelle: transformierte Daten aus <code>im3_transform.php</code>.
    </p>
  </div>
</body>
</html>