<?php
Anfrage::post("element", "seite", "position");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($seite) || !$DBS->existiert("website_seiten", $seite)) {
  Anfrage::addFehler(-3, true);
}

$elemente = [];
new Kern\Wurmloch("funktionen/website/elemente.php", array(), function($r) use (&$elemente){
  $elemente = array_merge($elemente, $r);
});

if(!isset($elemente[$element])) {
  Anfrage::addFehler(-3, true);
}

$klasse = new $elemente[$element](null, null, null, null);
Anfrage::post(...array_keys($klasse->getFelder()));
$klasse->postValidieren();
Anfrage::checkFehler();

$sql = [];
$werte = [];
foreach($elemente as $el => $c) {
  $sql[] = "SELECT el.position as position FROM website_$el as el WHERE el.seite = ?";
}
$sqlS = join("UNION", $sql);

$max = 0;
$DBS->anfrage("SELECT MAX(position) FROM ($sqlS) AS x", str_repeat("i", count($sql)), array_fill(0, count($sql), $seite))->werte($max);

if(!UI\Check::istZahl($position, 0, $max+1)) {
  Anfrage::addFehler(-3, true);
}

if (!$DSH_BENUTZER->hatRecht("website.inhalte.elemente.anlegen")) {
  Anfrage::addFehler(-4, true);
}

$parameter = [];
$backup    = [];
foreach($klasse->getFelder() as $spalte => $_) {
  $parameter["{$spalte}neu"] = "?";
  $werte[]  = $$spalte;
}

if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.aktivieren")) {
  foreach($klasse->getFelder() as $spalte => $_) {
    $parameter["{$spalte}aktuell"] = "?";
    $werte[] = $$spalte;
  }
}

$id = $DBS->neuerDatensatz("website_{$element}", array("seite" => "?", "position" => "?", "status" => "'a'"), "ii", $seite, $position);
$DBS->anfrage("INSERT INTO website_{$element}inhalte (element, sprache, ".join(", ", array_keys($parameter)).") VALUES (?, (SELECT id FROM website_sprachen WHERE a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)), ".join(", ", array_values($parameter)).");", "i".str_repeat("s", count($parameter)), ...array_merge([$id], $werte));
?>