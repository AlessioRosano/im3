<?php

// Transformations-Skript  als 'transform.php' einbinden
$jsonData = include('im3_transform.php');

// Dekodiert die JSON-Daten zu einem Array
$dataArray = json_decode($jsonData, true);

//hier weitermachen @alessio