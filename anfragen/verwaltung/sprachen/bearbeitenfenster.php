<?php
Anfrage::post("id");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id)) {
  Anfrage::addFehler(-3, true);
}

if (!$DSH_BENUTZER->hatRecht("website.sprachen.bearbeiten")) {
  Anfrage::addFehler(-4, true);
}

if(!$DBS->existiert("website_sprachen", "id = ?", "i", $id)) {
  Anfrage::addFehler(-3, true);
}

include_once __DIR__."/_details.php";

Anfrage::setRueck("Code", (string) spracheDetails($id));
?>
