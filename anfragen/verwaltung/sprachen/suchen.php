<?php
Anfrage::postSort();

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if (!$DSH_BENUTZER->hatRecht("website.sprachen.sehen")) {
  Anfrage::addFehler(-4, true);
}

$spalten = [["id"], ["{a2} as a2"], ["IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')) as bezeichnung"], ["IF((SELECT wert FROM website_einstellungen WHERE id = 0) = a2, '1', '0')"]];

$sql = "SELECT ?? FROM website_sprachen";

$ta = new Kern\Tabellenanfrage($sql, $spalten, $sortSeite, $sortDatenproseite, $sortSpalte, $sortRichtung);
$tanfrage = $ta->anfrage($DBS);
$anfrage = $tanfrage["Anfrage"];

$tabelle = new UI\Tabelle("dshVerwaltungSprachen", "website.verwaltung.sprachen.suchen", new UI\Icon(Website\Icons::SPRACHE), "Kennung", "Bezeichnung");
$tabelle->setSeiten($tanfrage);

while($anfrage->werte($id, $a2, $bezeichnung, $istStandard)) {
  $zeile = new UI\Tabelle\Zeile($id);
  $zeile["Kennung"]     = $a2;
  $zeile["Bezeichnung"] = $bezeichnung;

  if($istStandard) {
    $zeile->setIcon(new UI\Icon("fas fa-star"));
  }

  if(!$istStandard && $DSH_BENUTZER->hatRecht("website.sprachen.standardsprache")) {
    $knopf = new UI\MiniIconKnopf(new UI\Icon("fas fa-star"), "Zur Standardsprache machen", "Erfolg");
    $knopf ->addFunktion("onclick", "website.verwaltung.sprachen.standardsprache.fragen($id)");
    $zeile ->addAktion($knopf);
  }
  if($DSH_BENUTZER->hatRecht("website.sprachen.bearbeiten")) {
    $knopf = new UI\MiniIconKnopf(new UI\Icon(UI\Konstanten::BEARBEITEN), "Sprache bearbeiten");
    $knopf ->addFunktion("onclick", "website.verwaltung.sprachen.bearbeiten.fenster($id)");
    $zeile ->addAktion($knopf);
  }
  if($DSH_BENUTZER->hatRecht("website.sprachen.löschen")) {
    $knopf = new UI\MiniIconKnopf(new UI\Icon(UI\Konstanten::LOESCHEN), "Sprache löschen", "Warnung");
    $knopf ->addFunktion("onclick", "website.verwaltung.sprachen.loeschen.fragen($id)");
    $zeile ->addAktion($knopf);
  }

  $tabelle[] = $zeile;
}

Anfrage::setRueck("Code", (string) $tabelle);
?>