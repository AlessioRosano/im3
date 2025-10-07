<?php

/* ============================================================================
   HANDLUNGSANWEISUNG (transform.php)
   0) Schau dir die Rohdaten genau an und plane exakt, wie du die Daten umwandeln möchtest (auf Papier)
   1) Binde extract.php ein und erhalte das Rohdaten-Array.
   2) Definiere Mapping Koordinaten → Anzeigename (z. B. Bern/Chur/Zürich).
   3) Konvertiere Einheiten (z. B. °F → °C) und runde sinnvoll (Celsius = (Fahrenheit - 32) * 5 / 9).
   4) Leite eine einfache "condition" ab (z. B. sonnig/teilweise bewölkt/bewölkt/regnerisch).
   5) Baue ein kompaktes, flaches Array je Standort mit den Ziel-Feldern.
   6) Optional: Sortiere die Werte (z. B. nach Zeit), entferne irrelevante Felder.
   7) Validiere Pflichtfelder (location, temperature_celsius, …).
   8) Kodieren: json_encode(..., JSON_PRETTY_PRINT) → JSON-String.
   9) GIB den JSON-String ZURÜCK (return), nicht ausgeben – für den Load-Schritt.
  10) Fehlerfälle als Exception nach oben weiterreichen (kein HTML/echo).
   ============================================================================ */

// Bindet das Skript im3_extract.php für Rohdaten ein und speichere es in $data
$data = include('im3_extract.php');


// Überprüft, ob die Daten gültig sind
if (!is_array($data) || empty($data)) {
    throw new Exception("Keine gültigen Daten von im3_extract.php erhalten");
}

// Initialisiert ein Array, um die transformierten Daten zu speichern
$transformedData = [];

// Transformiert und fügt die notwendigen Informationen hinzu
foreach ($data as $row) {
    // Hole die Werte sicher heraus
    $measured_at_new = $row['measured_at_new'] ?? null;
    $datum_tag = $row['datum_tag'] ?? null;
    $data_right = isset($row['data_right']) ? (int)$row['data_right'] : 0;
    $data_left = isset($row['data_left']) ? (int)$row['data_left'] : 0;
    $summe = isset($row['summe']) ? (int)$row['summe'] : ($data_right + $data_left);

    // 3) Zeitformat anpassen: 2025-09-22T09:00:00.601000+00:00 → 2025-09-22 09:00:00
    $formatted_time = null;
    if ($measured_at_new) {
        $dateTime = new DateTime($measured_at_new);
        $formatted_time = $dateTime->format('Y-m-d H:i:s');
    }

    // 4) Neues Array pro Eintrag aufbauen
    $transformedData[] = [
        'measured_at_new' => $formatted_time,
        'datum_tag' => $datum_tag,
        'data_right' => $data_right,
        'data_left' => $data_left,
        'summe' => $summe
    ];
}

// Kodiert die transformierten Daten in JSON
$jsonData = json_encode($transformedData, JSON_PRETTY_PRINT);

// Gibt die JSON-Daten zurück, anstatt sie auszugeben
return $jsonData;

// Ergänzung Alessio
if (isset($data['results']) && is_array($data['results'])) {
    $data = $data['results'];
}
 

?>