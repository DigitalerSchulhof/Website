<?php
Anfrage::post("id");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id) || !$DBS->existiert("website_sprachen", $id)) {
  Anfrage::addFehler(-3, true);
}

if(!$DBS->existiert("website_sprachen", "id != ?", "i", $id)) {
  Anfrage::addFehler(13, true);
}

if (!$DSH_BENUTZER->hatRecht("website.sprachen.löschen")) {
  Anfrage::addFehler(-4, true);
}

$DBS->datensatzLoeschen("website_sprachen", $id);
// Standardsprache ändern
if($DBS->existiert("website_sprachen", "a2 = (SELECT wert FROM website_einstellungen WHERE inhalt = ['Standardsprache'])")) {
  $DBS->anfrage("UPDATE website_einstellungen SET wert = (SELECT a2 FROM website_sprachen LIMIT 1) WHERE inhalt = ['Standardsprache']");
}
?>