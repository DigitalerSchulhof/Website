<?php
Anfrage::post("element", "id");
if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id)) {
  Anfrage::addFehler(-3, true);
}

$elemente = [];
new Kern\Wurmloch("funktionen/website/elemente.php", array(), function($r) use (&$elemente){
  $elemente = array_merge($elemente, $r);
});

if(!isset($elemente[$element]) || !$DBS->existiert("website__$element", $id)) {
  Anfrage::addFehler(-3, true);
}

$klasse = new $elemente[$element](null, null, null, null);

if (!$DSH_BENUTZER->hatRecht("website.inhalte.elemente.löschen")) {
  Anfrage::addFehler(-4, true);
}

$parameter = [];
$backup    = [];
foreach($klasse->getFelder() as $spalte => $_) {
  $parameter["{$spalte}neu"] = "NULL";
  $backup[] = "{$spalte}alt = {$spalte}aktuell";
}

if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.aktivieren")) {
  foreach($klasse->getFelder() as $spalte => $_) {
    $parameter["{$spalte}aktuell"] = "NULL";
  }
}

$DBS->anfrage("UPDATE website__{$element} SET ".join(",", $backup)." WHERE id = ?", "i", $id);
$id = $DBS->datensatzBearbeiten("website__{$element}", "id = ?", $parameter, "i", ...[$id]);

?>