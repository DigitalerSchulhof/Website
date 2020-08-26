<?php
Anfrage::post("standardsprache");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if (!$DSH_BENUTZER->hatRecht("module.einstellungen")) {
  Anfrage::addFehler(-4, true);
}

$anf = $DBS->anfrage("SELECT id FROM website_sprachen WHERE alpha2 = ?", "s", $standardsprache);
if(!$anf->werte($id)) {
  Anfrage::addFehler(-3, true);
}

$sql = "UPDATE website_einstellungen SET wert = [?] WHERE inhalt = [?]";
$werte = [];
$werte[] = [$standardsprache,    "Standardsprache"];
$anfrage = $DBS->anfrage($sql, "ss", $werte);
?>
