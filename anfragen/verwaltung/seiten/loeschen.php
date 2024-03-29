<?php
Anfrage::post("id");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id) || !$DBS->existiert("website_seiten", $id)) {
  Anfrage::addFehler(-3, true);
}

if($DBS->existiert("website_seiten", "id = ? AND startseite = 1", "i", $id)) {
  Anfrage::addFehler(18, true);
}

if (!$DSH_BENUTZER->hatRecht("website.seiten.löschen")) {
  Anfrage::addFehler(-4, true);
}

$DBS->datensatzLoeschen("website_seiten", $id);
?>