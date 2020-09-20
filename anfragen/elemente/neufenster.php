<?php
Anfrage::post("element", "seite", "position", "sprache");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($seite) || !$DBS->existiert("website_seiten", $seite)) {
  Anfrage::addFehler(-3, true);
}

if (!$DBS->existiert("website_sprachen", "a2 = [?]", "s", $sprache)) {
  Anfrage::addFehler(-3, true);
}

$elemente = [];
new Kern\Wurmloch("funktionen/website/elemente.php", array(), function($r) use (&$elemente){
  $elemente = array_merge($elemente, $r);
});

if(!isset($elemente[$element])) {
  Anfrage::addFehler(-3, true);
}

$sql = [];
$werte = [];
foreach($elemente as $el => $c) {
  $sql[] = "SELECT el.position as position FROM website__$el as el WHERE el.seite = ?";
}
$sqlS = join(" UNION ", $sql);

$max = 0;
$DBS->anfrage("SELECT MAX(position) FROM ($sqlS) AS x", str_repeat("i", count($sql)), array_fill(0, count($sql), $seite))->werte($max);

if(!UI\Check::istZahl($position, 0, $max+1)) {
  Anfrage::addFehler(-3, true);
}

if (!$DSH_BENUTZER->hatRecht("website.inhalte.elemente.anlegen")) {
  Anfrage::addFehler(-4, true);
}

include_once __DIR__."/_details.php";

Anfrage::setRueck("Code", (string) elementDetails(array("tabelle" => $element, "klasse" => $elemente[$element], "sprache" => $sprache, "position" => $position, "seite" => $seite), null, $position));
?>
