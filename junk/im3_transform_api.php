<?php

/* ============================================================================
   TRANSFORM: nimmt Array von Datensätzen und gibt JSON-String zurück
   ============================================================================ */

// Rohdaten holen
$data = include __DIR__ . '/im3_extract.php';

// Falls Extract wider Erwarten die ganze Payload liefert:
if (isset($data['results']) && is_array($data['results'])) {
    $data = $data['results'];
}

// Validieren
if (!is_array($data) || empty($data)) {
    throw new Exception("Keine gültigen Daten von im3_extract.php erhalten");
}

// Transformieren
$transformedData = [];
$tzZurich = new DateTimeZone('Europe/Zurich');

foreach ($data as $row) {
    // Sicher ziehen
    $measured_at_new = $row['measured_at_new'] ?? null; // z. B. 2025-10-07T07:00:00.071000+00:00
    $datum_tag       = $row['datum_tag'] ?? null;
    $data_right      = isset($row['data_right']) ? (int)$row['data_right'] : 0;
    $data_left       = isset($row['data_left'])  ? (int)$row['data_left']  : 0;
    $summe           = isset($row['summe'])      ? (int)$row['summe']      : ($data_right + $data_left);

    // Zeit robust konvertieren → Europe/Zurich, "Y-m-d H:i:s"
    $formatted_time = null;
    if ($measured_at_new) {
        try {
            $dt = new DateTimeImmutable($measured_at_new); // ISO8601 inkl. TZ
        } catch (Throwable $e) {
            // Fallback: plain ohne TZ
            $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $measured_at_new, new DateTimeZone('UTC'));
        }
        if ($dt) {
            $formatted_time = $dt->setTimezone($tzZurich)->format('Y-m-d H:i:s');
        }
    }

    // Zielstruktur (beibehaltene Keys)
    $transformedData[] = [
        'measured_at_new' => $formatted_time,
        'datum_tag'       => $datum_tag,
        'data_right'      => $data_right,
        'data_left'       => $data_left,
        'summe'           => $summe
    ];
}

// JSON bauen
$jsonData = json_encode($transformedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($jsonData === false) {
    throw new Exception('JSON-Fehler: ' . json_last_error_msg());
}

return $jsonData;