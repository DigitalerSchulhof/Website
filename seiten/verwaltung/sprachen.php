<?php
$SEITE = new Kern\Seite("Sprachen", "website.sprachen.sehen");

$spalte = new UI\Spalte("A1", new UI\SeitenUeberschrift("Sprachen"));

$tabelle = new UI\Tabelle("dshVerwaltungSprachen", "website.verwaltung.sprachen.suchen", null, "Kennung", "Bezeichnung");
$tabelle ->setAutoladen(true);

$spalte[] = $tabelle;

$knoepfe = [];
if ($DSH_BENUTZER->hatRecht("website.sprachen.anlegen")) {
  $knopf      = new UI\IconKnopf(new UI\Icon(UI\Konstanten::NEU), "Sprache anlegen", "Erfolg");
  $knopf      ->addFunktion("onclick", "website.verwaltung.sprachen.neu.fenster()");
  $knoepfe[]  = $knopf;
}
if($DSH_BENUTZER->hatRecht("website.sprachen.fehlermeldungen")) {
  $knopf      = new UI\IconKnopf(new UI\Icon(Website\Icons::FEHLERMELDUNG), "Fehlermeldungen bearbeiten");
  $knopf      ->addFunktion("href", "Schulhof/Verwaltung/Sprachen/Fehlermeldungen");
  $knoepfe[]  = $knopf;
}

if (count($knoepfe) > 0) {
  $spalte[] = new UI\Absatz(join(" ", $knoepfe));
}

$SEITE[] = new UI\Zeile($spalte);
?>
