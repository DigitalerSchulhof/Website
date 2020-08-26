<?php
Anfrage::post("id");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id) || !$DBS->existiert("website_sprachen", "id = ?", "i", $id)) {
  Anfrage::addFehler(-3, true);
}

if (!$DSH_BENUTZER->hatRecht("website.sprachen.standardsprache")) {
  Anfrage::addFehler(-4, true);
}

$DBS->anfrage("UPDATE website_einstellungen SET wert = (SELECT a2 FROM website_sprachen WHERE id = ? LIMIT 1) WHERE inhalt = ['Standardsprache']", "i", $id);

?>