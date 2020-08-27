<?php
$SEITE = new Kern\Seite("Seiten", "website.seiten.sehen");

$spalte = new UI\Spalte("A1", new UI\SeitenUeberschrift("Seiten"));

$tabelle = new UI\Tabelle("dshVerwaltungSeiten", "website.verwaltung.seiten.suchen", new UI\Icon(Website\Icons::SEITE), "Bezeichnung", "Status");
$tabelle ->setAutoladen(true);

$spalte[] = $tabelle;

$knoepfe = [];
if ($DSH_BENUTZER->hatRecht("website.seiten.anlegen")) {
  $knopf      = new UI\IconKnopf(new UI\Icon (UI\Konstanten::NEU), "Seite anlegen", "Erfolg");
  $knopf      ->addFunktion("onclick", "website.verwaltung.seiten.neu.fenster()");
  $knoepfe[]   = $knopf;
}

if (count($knoepfe) > 0) {
  $spalte[] = new UI\Absatz(join(" ", $knoepfe));
}

$SEITE[] = new UI\Zeile($spalte);
?>
