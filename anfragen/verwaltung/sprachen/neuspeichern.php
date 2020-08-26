<?php
Anfrage::post("a2", "name", "namestandard", "alt", "aktuell", "neu", "sehen", "bearbeiten", "fehler", "startseite");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istText($a2) || strlen($a2) !== 2) {
  Anfrage::addFehler(1);
}

if(!UI\Check::istText($name)) {
  Anfrage::addFehler(2);
}

if(!UI\Check::istText($namestandard)) {
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

if(!UI\Check::istText($startseite)) {
  Anfrage::addFehler(10);
}

if($DBS->existiert("website_sprachen", "a2 = [?]", "s", $a2)) {
  Anfrage::addFehler(11);
}

if($DBS->existiert("website_sprachen", "name = [?]", "s", $name)) {
  Anfrage::addFehler(12);
}

if($DBS->existiert("website_sprachen", "namestandard = [?]", "s", $namestandard)) {
  Anfrage::addFehler(13);
}

Anfrage::checkFehler();


if (!$DSH_BENUTZER->hatRecht("website.sprachen.anlegen")) {
  Anfrage::addFehler(-4, true);
}

$id = $DBS->neuerDatensatz("website_sprachen",
  array("a2" => "[?]", "name" => "[?]", "namestandard" => "[?]", "alt" => "[?]", "aktuell" => "[?]", "neu" => "[?]", "sehen" => "[?]", "bearbeiten" => "[?]", "fehler" => "[?]", "startseite" => "[?]"),
  "ssssssssss", $a2, $name, $namestandard, $alt, $aktuell, $neu, $sehen, $bearbeiten, $fehler, $startseite);

?>