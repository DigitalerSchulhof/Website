<?php
if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if (!$DSH_BENUTZER->hatRecht("website.sprachen.anlegen")) {
  Anfrage::addFehler(-4, true);
}

$spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Neue Sprache anlegen"));

include_once __DIR__."/_details.php";

$spalte[] = spracheDetails(null);

$code = new UI\Fenster("dshVerwaltungSpracheNeu", "Neue Sprache anlegen", new UI\Zeile($spalte), true, true);
$code->addFensteraktion(UI\Knopf::schliessen("dshVerwaltungSpracheNeu"));

Anfrage::setRueck("Code", (string) $code);
?>
