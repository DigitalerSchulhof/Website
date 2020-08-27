<?php
$SEITE = new Kern\Seite("Seiten", "website.seiten.sehen");

$spalte = new UI\Spalte("A1", new UI\SeitenUeberschrift("Seiten"));

$tabelle = new UI\Tabelle("dshVerwaltungSeiten", "website.verwaltung.seiten.suchen", new UI\Icon(Website\Icons::SPRACHE), "Kennung", "Bezeichnung");
$tabelle ->setAutoladen(true);

$spalte[] = $tabelle;

$knoepfe = [];
if ($DSH_BENUTZER->hatRecht("website.sprachen.anlegen")) {
  $knopf      = new UI\IconKnopf(new UI\Icon (UI\Konstanten::NEU), "Sprache anlegen", "Erfolg");
  $knopf      ->addFunktion("onclick", "website.verwaltung.sprachen.neu.fenster()");
  $knoepfe[]   = $knopf;
}

if (count($knoepfe) > 0) {
  $spalte[] = new UI\Absatz(join(" ", $knoepfe));
}

$SEITE[] = new UI\Zeile($spalte);
?>
