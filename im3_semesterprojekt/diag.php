<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');

// Basis-Infos
echo 'cwd: ' . getcwd() . PHP_EOL;

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = rtrim(dirname($_SERVER['REQUEST_URI'] ?? '/'), '/');
echo 'URL should be: https://' . $host . $base . '/' . PHP_EOL . PHP_EOL;

// Prüfliste – passe Dateinamen gern an
$files = [
  'kybunpark.png',
  'ball.png',
  'im3_index.php',
  'index.php',
  'im3_styles.css',
  'im3_styles.css', // doppelt ist egal
];

foreach ($files as $f) {
  $exists = file_exists($f);
  echo str_pad($f, 20) . ' exists? ' . ($exists ? 'YES' : 'NO');
  if ($exists) {
    echo ' | size: ' . @filesize($f) . ' bytes';
    echo ' | readable: ' . (is_readable($f) ? 'YES' : 'NO');

    // Bilddetails, falls es ein Bild ist
    if (preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $f)) {
      $info = @getimagesize($f);
      if ($info) {
        echo ' | image: ' . $info[0] . 'x' . $info[1];
      }
    }
  }
  echo PHP_EOL;
}

echo PHP_EOL . 'Directory listing:' . PHP_EOL;
$entries = array_values(array_diff(scandir('.'), ['.','..']));
foreach ($entries as $e) {
  echo ' - ' . $e . PHP_EOL;
}