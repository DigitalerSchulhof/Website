<?php
Anfrage::post("element", "id", "sprache");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id)) {
  Anfrage::addFehler(-3, true);
}

if(!$DBS->existiert("website_sprachen", "a2 = [?]", "s", $sprache)) {
  Anfrage::addFehler(-3, true);
}

$elemente = [];
new Kern\Wurmloch("funktionen/website/elemente.php", array(), function($r) use (&$elemente){
  $elemente = array_merge($elemente, $r);
});

if(!isset($elemente[$element]) || !$DBS->existiert("website_$element", $id)) {
  Anfrage::addFehler(-3, true);
}

$klasse = new $elemente[$element](null, null, null, null);
Anfrage::post(...array_keys($klasse->getFelder()));
$klasse->postValidieren();
Anfrage::checkFehler();

if (!$DSH_BENUTZER->hatRecht("website.inhalte.elemente.bearbeiten")) {
  Anfrage::addFehler(-4, true);
}

$parameter = [];
$backup    = [];
foreach($klasse->getFelder() as $spalte => $_) {
  $parameter["{$spalte}neu"] = "?";
  $werte[]  = $$spalte;
  $backup[] = "{$spalte}alt = {$spalte}aktuell";
}

if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.aktivieren")) {
  foreach($klasse->getFelder() as $spalte => $_) {
    $parameter["{$spalte}aktuell"] = "?";
    $werte[] = $$spalte;
  }
}

$DBS->anfrage("UPDATE website_{$element}inhalte SET ".join(",", $backup)." WHERE element = ? AND sprache = (SELECT id FROM website_sprachen WHERE a2 = [?])", "is", $id, $sprache);
$DBS->anfrage("INSERT IGNORE INTO website_{$element}inhalte (element, sprache) VALUES (?, (SELECT id FROM website_sprachen WHERE a2 = [?]))", "is", $id, $sprache);
$id = $DBS->datensatzBearbeiten("website_{$element}inhalte", "element = ? AND sprache = (SELECT id FROM website_sprachen WHERE a2 = [?])",
  $parameter,
  str_repeat("s", count($parameter))."is", ...array_merge($werte, [$id, $sprache]));

?>