<?php

$name = "Alessio und Sarina";
echo "Hallo $name, wie geht's?";



$a = 5;
$b = 10;
echo $a+$b;
echo "ja mudda";

function multiply($a, $b) {
    return $a*$b;
}


echo multiply(12, 3);



$note = 3.75;
if ($note > 4) {
    echo "Bestanden";
} elseif ($note < 4&& $note >= 3.5) {
    echo "nahprüefig";
}
else {
    echo "Nicht Bestanden";
}


// -- arrays -------------------------------------------------------------

$tests = [5, 4, 3, 2, 1];
echo '<pre>';
print_r($tests);
echo '</pre>';


