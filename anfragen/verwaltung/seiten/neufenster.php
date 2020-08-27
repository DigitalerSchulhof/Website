<?php
Anfrage::post("id");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if (!$DSH_BENUTZER->hatRecht("website.seiten.anlegen")) {
  Anfrage::addFehler(-4, true);
}

include_once __DIR__."/_details.php";

Anfrage::setRueck("Code", (string) seiteDetails(null, $id));
?>
