<?php
Anfrage::post("sprache", "titel403", "inhalt403", "titel404", "inhalt404");
if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!$DBS->existiert("website_sprachen", "a2 = [?]", "s", $sprache)) {
  Anfrage::addFehler(-3, true);
}

$DBS->anfrage("SELECT IF((SELECT a2 FROM website_sprachen WHERE a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)) = [?], 1, 0)", "s", $sprache)
      ->werte($min);

if(!UI\Check::istText($titel403, $min)) {
  Anfrage::addFehler(21);
}
if(!UI\Check::istEditor($inhalt403)) {
  Anfrage::addFehler(22);
}
if(!UI\Check::istText($titel404, $min)) {
  Anfrage::addFehler(21);
}
if(!UI\Check::istEditor($inhalt404)) {
  Anfrage::addFehler(22);
}

Anfrage::checkFehler();

if (!$DSH_BENUTZER->hatRecht("website.sprachen.fehlermeldungen")) {
  Anfrage::addFehler(-4, true);
}

$DBS->datensatzBearbeiten("website_fehlermeldungen", "sprache = (SELECT id FROM website_sprachen WHERE a2 = [?]) AND fehler = '403'", array("titel" => "[?]", "inhalt" => "[?]"), "sss", $titel403, $inhalt403, $sprache);
$DBS->datensatzBearbeiten("website_fehlermeldungen", "sprache = (SELECT id FROM website_sprachen WHERE a2 = [?]) AND fehler = '404'", array("titel" => "[?]", "inhalt" => "[?]"), "sss", $titel404, $inhalt404, $sprache);
?>