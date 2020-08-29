<?php
Anfrage::post("id");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id) || !$DBS->existiert("website_seiten", "id = ? AND art = 'i' AND status = 'a' AND zugehoerig IS NULL", "i", $id)) {
  Anfrage::addFehler(-3, true);
}

if (!$DSH_BENUTZER->hatRecht("website.seiten.startseite")) {
  Anfrage::addFehler(-4, true);
}

$DBS->anfrage("UPDATE website_seiten SET startseite = 0");
$DBS->datensatzBearbeiten("website_seiten", $id, array("startseite" => "1"));
?>