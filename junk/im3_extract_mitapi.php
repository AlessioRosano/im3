<?php
/* ============================================================================
   EXTRACT: holt Datensätze als Array (keine Ausgabe)
   ============================================================================ */

function fetchDatenStadtSG(): array
{
    $base  = 'https://daten.stadt.sg.ch/api/explore/v2.1/catalog/datasets/fussganger-stgaller-innenstadt-vadianstrasse/records';
    $query = http_build_query([
        'order_by' => 'measured_at_new DESC', // genaue Zeit, neueste zuerst
        'limit'    => 100,                    // v2.1: max 100
    ]);
    $url = $base . '?' . $query;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'User-Agent: im3-transform/1.0',
        ],
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL-Fehler: ' . $err);
    }

    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http < 200 || $http >= 300) {
        throw new Exception('HTTP-Fehlerstatus: ' . $http);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new Exception('JSON-Decode fehlgeschlagen: ' . json_last_error_msg());
    }

    // Nur die Datensätze an Transform zurückgeben
    if (isset($decoded['results']) && is_array($decoded['results'])) {
        return $decoded['results'];
    }
    return [];
}

return fetchDatenStadtSG();

