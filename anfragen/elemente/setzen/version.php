<?php
/** @var \Kern\DB $DBS */

Anfrage::post("element", "id", "version");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id)) {
  Anfrage::addFehler(-3, true);
}

if (!in_array($version, ["a", "n"])) {
    Anfrage::addFehler(-3, true);
}

$elemente = [];
new Kern\Wurmloch("funktionen/website/elemente.php", array(), function($r) use (&$elemente){
  $elemente = array_merge($elemente, $r);
});

if(!isset($elemente[$element]) || !$DBS->existiert("website__$element", $id)) {
  Anfrage::addFehler(-3, true);
}
/** @var Website\Element $klasse */
$klasse = new $elemente[$element](null, null, null, null);

if ($version == "a") {
    if (!$DSH_BENUTZER->hatRecht("website.inhalte.versionen.alt.aktivieren")) {
        Anfrage::addFehler(-4, true);
    }
} else {
    if (!$DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.aktivieren")) {
        Anfrage::addFehler(-4, true);
    }
}
$sql = [];
if ($version == "a") {
    foreach ($klasse->getFelder() as $sp => $_) {
        $sql[] = "{$sp}aktuell = {$sp}alt";
    }
    $sql[] = "statusaktuell = statusalt";
} else {
    foreach ($klasse->getFelder() as $sp => $_) {
        $sql[] = "{$sp}alt = {$sp}aktuell, {$sp}aktuell = {$sp}neu";
    }
    $sql[] = "statusalt = statusaktuell, statusaktuell = statusneu";
}
$DBS->anfrage("UPDATE website__$element SET " . join(",", $sql) . " WHERE id = ?", "i", $id);
$DBS->anfrage("DELETE FROM website__$element WHERE statusalt = 'l' AND statusaktuell = 'l' AND statusneu = 'l'");