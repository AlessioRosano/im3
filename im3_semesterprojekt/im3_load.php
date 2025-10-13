<?php
declare(strict_types=1);

try {
  $json = include __DIR__ . '/im3_transform.php';
  $rows = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
  http_response_code(502);
  header('Content-Type: text/plain; charset=utf-8');
  echo "Daten konnten nicht geladen werden:\n" . $e->getMessage();
  exit;
}

$rows = is_array($rows) ? $rows : [];
$maxRows = 200;
if (count($rows) > $maxRows) {
  $rows = array_slice($rows, 0, $maxRows);
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Fussgänger-Zählungen – Übersicht</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --pad:.6rem; --border:#e5e7eb; --bg:#fafafa; --muted:#6b7280; }
    body { font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; margin:0; color:#111827; }
    .wrap { max-width:1100px; margin:2rem auto; padding:0 1rem; }
    h1 { margin:0 0 .25rem; font-size:1.4rem; }
    .meta { color:var(--muted); margin:0 0 1rem; }
    table { width:100%; border-collapse:collapse; border:1px solid var(--border); background:#fff; }
    thead th { text-align:left; font-weight:600; border-bottom:1px solid var(--border); padding:var(--pad); background:var(--bg); }
    tbody td { padding:var(--pad); border-bottom:1px solid var(--border); }
    tbody tr:hover { background:#fcfcfd; }
    .num { text-align:right; font-variant-numeric: tabular-nums; }
    .tag { display:inline-block; font-size:.8rem; padding:.1rem .4rem; border:1px solid var(--border); border-radius:.375rem; color:#374151; background:#fff; }
    .footer { margin-top:.75rem; color:var(--muted); font-size:.9rem; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Fussgänger-Zählungen</h1>
    <p class="meta">Einträge: <?= number_format(count($rows), 0, ',', '\'') ?> · neueste zuerst (max. <?= (int)$maxRows ?>)</p>

    <?php if (!$rows): ?>
      <p class="meta">Noch keine Daten vorhanden.</p>
    <?php else: ?>
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
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['measured_at_new'] ?? '') ?></td>
              <td><span class="tag"><?= htmlspecialchars($r['datum_tag'] ?? '') ?></span></td>
              <td class="num"><?= (int)($r['data_right'] ?? 0) ?></td>
              <td class="num"><?= (int)($r['data_left'] ?? 0) ?></td>
              <td class="num"><?= (int)($r['summe'] ?? 0) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p class="footer">Quelle: DB → <code>im3_extract.php</code> → <code>im3_transform.php</code>.</p>
  </div>
</body>
</html>