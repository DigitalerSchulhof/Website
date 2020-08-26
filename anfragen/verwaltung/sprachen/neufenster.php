<?php
if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if (!$DSH_BENUTZER->hatRecht("website.sprachen.anlegen")) {
  Anfrage::addFehler(-4, true);
}

include_once __DIR__."/_details.php";

Anfrage::setRueck("Code", (string) spracheDetails(null));
?>
