<?php
Anfrage::post("id", "status");
if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!in_array($status, ["a", "i"])) {
  Anfrage::addFehler(-3, true);
}

if(!UI\Check::istZahl($id) || !$DBS->existiert("website_seiten", $id)) {
  Anfrage::addFehler(-3, true);
}

if($status != "a") {
  // Ist Startseite
  if($DBS->existiert("website_seiten", "id = ? AND startseite = 1", "i", $id)) {
    Anfrage::addFehler(19);
  }
}

Anfrage::checkFehler();

if (!$DSH_BENUTZER->hatRecht("website.seiten.bearbeiten")) {
  Anfrage::addFehler(-4, true);
}

$DBS->datensatzBearbeiten("website_seiten", $id, array("status" => "?"), "s", $status);
?>