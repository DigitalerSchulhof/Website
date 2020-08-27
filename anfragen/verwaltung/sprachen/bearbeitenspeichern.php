<?php
Anfrage::post("id", "a2", "name", "namestandard", "alt", "aktuell", "neu", "sehen", "bearbeiten", "fehler");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id) || !$DBS->existiert("website_sprachen", $id)) {
  Anfrage::addFehler(-3, true);
}

if(!UI\Check::istText($a2) || strlen($a2) !== 2) {
  Anfrage::addFehler(1);
}

if(!UI\Check::istText($name)) {
  Anfrage::addFehler(2);
}

if(!UI\Check::istText($namestandard, 0)) {
  Anfrage::addFehler(3);
}

if(!UI\Check::istText($alt)) {
  Anfrage::addFehler(4);
}

if(!UI\Check::istText($aktuell)) {
  Anfrage::addFehler(5);
}

if(!UI\Check::istText($neu)) {
  Anfrage::addFehler(6);
}

if(!UI\Check::istText($sehen)) {
  Anfrage::addFehler(7);
}

if(!UI\Check::istText($bearbeiten)) {
  Anfrage::addFehler(8);
}

if(!UI\Check::istText($fehler)) {
  Anfrage::addFehler(9);
}

if($DBS->existiert("website_sprachen", "a2 = [?] AND id != ?", "si", $a2, $id)) {
  Anfrage::addFehler(10);
}

if($DBS->existiert("website_sprachen", "name = [?] AND id != ?", "si", $name, $id)) {
  Anfrage::addFehler(11);
}

if($DBS->existiert("website_sprachen", "namestandard = [?] AND id != ?", "si", $namestandard, $id)) {
  Anfrage::addFehler(12);
}

Anfrage::checkFehler();


if (!$DSH_BENUTZER->hatRecht("website.sprachen.bearbeiten")) {
  Anfrage::addFehler(-4, true);
}

$id = $DBS->datensatzBearbeiten("website_sprachen", $id,
  array("a2" => "[?]", "name" => "[?]", "namestandard" => "[?]", "alt" => "[?]", "aktuell" => "[?]", "neu" => "[?]", "sehen" => "[?]", "bearbeiten" => "[?]", "fehler" => "[?]"),
  "sssssssss", $a2, $name, $namestandard, $alt, $aktuell, $neu, $sehen, $bearbeiten, $fehler);

?>