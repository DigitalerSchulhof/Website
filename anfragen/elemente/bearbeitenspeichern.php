<?php
/** @var \Kern\DB $DBS */

Anfrage::post("element", "id", "status");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id)) {
  Anfrage::addFehler(-3, true);
}

if (!in_array($status, ["a", "i"])) {
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
Anfrage::post(...array_keys($klasse->getFelder()));
$klasse->postValidieren();
Anfrage::checkFehler();

if (!$DSH_BENUTZER->hatRecht("website.inhalte.elemente.bearbeiten")) {
  Anfrage::addFehler(-4, true);
}

$parameter = [];
$backup    = [];
$werte     = [];

$parameter["statusneu"] = "?";
$backup[] = "statusalt = statusaktuell";
$werte[] = $status;
foreach($klasse->getFelder() as $spalte => $_) {
  $parameter["{$spalte}neu"] = "[?]";
  $werte[]  = $$spalte;
  $backup[] = "{$spalte}alt = {$spalte}aktuell";
}

if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.aktivieren")) {
  foreach($klasse->getFelder() as $spalte => $_) {
    $parameter["{$spalte}aktuell"] = "[?]";
    $werte[] = $$spalte;
  }
  $parameter["statusaktuell"] = "?";
  $werte[] = $status;
}

$DBS->anfrage("UPDATE website__$element SET ".join(",", $backup)." WHERE id = ?", "i", $id);
$DBS->datensatzBearbeiten("website__$element", "id = ?",
  $parameter,
  str_repeat("s", count($parameter))."i", ...array_merge($werte, [$id]));
$klasse->setEId($id);
$klasse->nachSpeichern();
?>