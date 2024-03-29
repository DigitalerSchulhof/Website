<?php
Anfrage::post("a2", "name", "namestandard", "alt", "aktuell", "neu", "sehen", "bearbeiten", "fehler");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
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

if($DBS->existiert("website_sprachen", "a2 = [?]", "s", $a2)) {
  Anfrage::addFehler(10);
}

if($DBS->existiert("website_sprachen", "name = [?]", "s", $name)) {
  Anfrage::addFehler(11);
}

if(strlen($namestandard) > 0 && $DBS->existiert("website_sprachen", "namestandard = [?]", "s", $namestandard)) {
  Anfrage::addFehler(12);
}

Anfrage::checkFehler();


if (!$DSH_BENUTZER->hatRecht("website.sprachen.anlegen")) {
  Anfrage::addFehler(-4, true);
}

$id = $DBS->neuerDatensatz("website_sprachen",
  array("a2" => "[?]", "name" => "[?]", "namestandard" => "[?]", "alt" => "[?]", "aktuell" => "[?]", "neu" => "[?]", "sehen" => "[?]", "bearbeiten" => "[?]", "fehler" => "[?]"),
  "sssssssss", $a2, $name, $namestandard, $alt, $aktuell, $neu, $sehen, $bearbeiten, $fehler);
$DBS->anfrage("INSERT INTO website_fehlermeldungen (sprache, fehler, titel, inhalt) VALUES (?, '403', NULL, NULL), (?, '404', NULL, NULL)", "ii", $id, $id);
?>