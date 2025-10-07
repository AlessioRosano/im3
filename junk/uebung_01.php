<?php
// -- VARIABLEN und echo -------------------------------------------------------------
$item1 = "Brot";
$price1 = 4.90;      // CHF
$item2 = "Banane";
$price2 = 2.20;

$mwst = 0.08;

// TODO: Zwischensumme berechnen
$subtotal = $price1 + $price2;

// TODO: MWST-Betrag berechnen
$tax = $subtotal * $mwst;

// TODO: Total berechnen
$total = $subtotal + $tax;

// TODO: Ausgabe ohne Formatierung"
$output = "{$item1}: {$price1} | <br/> ";
$output .= "{$item2}: {$price2} | <br/> ";
$output .= "MWST: {$tax} | <br/> ";
$output .= "Total: {$total} | <br/>";
echo $output;

echo  "---------- ---------- ---------- ----------<br/><br/>";

// TODO: Ausgabe mit number_format (2 Nachkommastellen)
$output_format = "{$item1}: " . number_format($price1, 2) . " | <br/> ";
$output_format .= "{$item2}: " . number_format($price2, 2) . " | <br/> ";
$output_format .= "MWST: " . number_format($tax, 2) . " | <br/> ";
$output_format .= "Total: " . number_format($total, 2) . "<br/>";
echo $output_format;


// linie trennen
echo  "---------- ---------- ---------- ----------<br/><br/>";


// -- FUNKTIONEN -------------------------------------------------------------

function add_mwst($netto, $satz = 0.08)
{
  // TODO: Brutto berechnen und zurückgeben
  return $netto * (1 + $satz);
}

function kassenbon($name, $betrag)
{
  // TODO: formatierte Zeile zurückgeben (mit number_format)
  return "{$name}: " . number_format($betrag, 2);
}

$brutto = add_mwst(9.99); // Default 0.08 %
echo kassenbon("Lea", $brutto) . " CHF <br/>";


echo  "---------- ---------- ---------- ----------<br/><br/>";

// -- BEDINGUNGEN - ARRAYS - SCHLEIFEN-------------------------------------------------------------


function bewerte_Temperatur($temperatur)
{
  // TODO: if/elseif/else implementieren
  if ($temperatur < 0) {
    return "eiskalt";
  } elseif ($temperatur < 10) {
    return "kalt";
  } elseif ($temperatur  < 20) {
    return "mild";
  } else {
    return "warm";
  }
}

$werte = [-5, 3.5, 12, 21.2];
foreach ($werte as $t) {
  echo $t . "°C → " . bewerte_Temperatur($t) . "<br/>";
}




echo  "---------- ---------- ---------- ----------<br/><br/>";


$wg = ["Barbie", "Ken", "Allan", "President Barbie", "Anja"];

// 1) Filtern: mind. 5 Buchstaben
$long_names = array_filter($wg, fn($name) => strlen($name) >= 5);

// 2) Mappen: Ausrufezeichen anhängen
$shout = array_map(fn($name) => $name . "!", $long_names);

// 3) Reduzieren: Gesamtanzahl der Buchstaben (ohne "!") ermitteln
$totalLetters = array_reduce($long_names, fn($c, $n) => $c + strlen($n), 0);

// TODO: Ausgabe
print_r($shout);
echo "Total Buchstaben: " . $totalLetters . "<br/>";

// Bonus: Füge ein zweites Array hinzu und merge es (array_merge oder Spread).


echo  "---------- ---------- ---------- ----------<br/><br/>";

$numbers = [10, -5, 20, -1, 3, 0, 30];

foreach ($numbers as $n) {
  // TODO: negative überspringen
  if ($n < 0) {
    continue;
  }
  // TODO: bei 0 abbrechen
  if ($n === 0) {
    break;
  }

  // TODO: positive ausgeben
  if ($n > 0) {
    echo $n . " ";
  }
}

echo  "---------- ---------- ---------- ----------<br/><br/>";

$i = 1;
while ($i <= 10) {
  echo $i . " ";
  $i++;
}

echo  "---------- ---------- ---------- ----------<br/><br/>";

// Bonus: Schreibe eine for-Schleife, die jeden 3. Wert (3, 6, 9, ...) ausgibt.
for ($i = 3; $i <= 30; $i += 3) {
  echo $i . " ";
}
