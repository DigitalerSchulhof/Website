<?php
Anfrage::post("element", "id");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

$elemente = [];
new Kern\Wurmloch("funktionen/website/elemente.php", array(), function($r) use (&$elemente){
  $elemente = array_merge($elemente, $r);
});

if(!isset($elemente[$element])) {
  Anfrage::addFehler(-3, true);
}

if(!UI\Check::istZahl($id)) {
  Anfrage::addFehler(-3, true);
}

if (!$DSH_BENUTZER->hatRecht("website.inhalte.elemente.bearbeiten")) {
  Anfrage::addFehler(-4, true);
}

if(!$DBS->existiert("website__$element", $id)) {
  Anfrage::addFehler(-3, true);
}

include_once __DIR__."/_details.php";

Anfrage::setRueck("Code", (string) elementDetails(array("tabelle" => $element, "klasse" => $elemente[$element]), $id));
?>
