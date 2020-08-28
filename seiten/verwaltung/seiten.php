<?php
$SEITE = new Kern\Seite("Seiten", "website.seiten.sehen");

$spalte = new UI\Spalte("A1", new UI\SeitenUeberschrift("Seiten"));

$tabelle = new UI\Tabelle("dshVerwaltungSeiten", "website.verwaltung.seiten.suchen", new UI\Icon(Website\Icons::SEITE), "Bezeichnung", "Pfad", "Status");
$tabelle ->setAutoladen(true);

$spalte[] = new UI\Meldung("Sprachabhängig", "Seitenbezeichnungen und -pfade sind von der ausgewählten Sprache abhängig.<br><i>Kursive</i> Einträge stammen von der Standardsprache.", "Information");
$spalte[] = $tabelle;

$standard   = Kern\Einstellungen::laden("Website", "Standardsprache");
$sprachwahl = new UI\Auswahl("dshVerwaltungSeitenSprachwahl", $standard);
$sprachwahl ->setTitel("Anzeigesprache");
$sprachwahl ->addFunktion("oninput", "ui.tabelle.sortieren('dshVerwaltungSeiten')");

$anf = $DBS->anfrage("SELECT {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')) as bezeichnung FROM website_sprachen");
while($anf->werte($a2, $bez)) {
  $sprachwahl->add($bez, $a2, $standard == $a2);
}
$sprachwahl->addKlasse("dshUiEingabefeldKlein");
$sprachwahl->setStyle("float", "right");


$knoepfe = [$sprachwahl];

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
