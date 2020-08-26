<?php
Anfrage::post("id");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!UI\Check::istZahl($id)) {
  Anfrage::addFehler(-3, true);
}

if (!$DSH_BENUTZER->hatRecht("website.sprachen.bearbeiten")) {
  Anfrage::addFehler(-4, true);
}

if(!$DBS->existiert("website_sprachen", "id = ?", "i", $id)) {
  Anfrage::addFehler(-3, true);
}

$spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Sprache bearbeiten"));

include_once __DIR__."/_details.php";

$spalte[] = spracheDetails($id);

$code = new UI\Fenster("dshVerwaltungSpracheBearbeiten", "Sprache bearbeiten", new UI\Zeile($spalte), true, true);
$code->addFensteraktion(UI\Knopf::schliessen("dshVerwaltungSpracheBearbeiten"));

Anfrage::setRueck("Code", (string) $code);
?>
