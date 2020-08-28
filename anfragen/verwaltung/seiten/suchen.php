<?php
Anfrage::postSort();
Anfrage::post("sprache");

if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!$DBS->existiert("website_sprachen", "a2 = [?]", "s", $sprache)) {
  Anfrage::addFehler(-3, true);
}

if (!$DSH_BENUTZER->hatRecht("website.seiten.sehen")) {
  Anfrage::addFehler(-4, true);
}

$spalten = [["ws.id as id"], ["ws.art as art"], ["{wsd.bezeichnung} as bezeichnung"], ["ws.status as status"], ["{wsd.pfad} as pfad"], ["IF(ws.zugehoerig IS NULL, '0', '1')"], ["ws.startseite"]];

$sql = "SELECT ?? FROM website_seiten as ws JOIN website_seitendaten as wsd ON wsd.seite = ws.id WHERE wsd.sprache = (SELECT id FROM website_sprachen WHERE a2 = [?])";

$ta = new Kern\Tabellenanfrage($sql, $spalten, $sortSeite, $sortDatenproseite, $sortSpalte, $sortRichtung);
$tanfrage = $ta->anfrage($DBS, "s", $sprache);
$anfrage = $tanfrage["Anfrage"];

$tabelle = new UI\Tabelle("dshVerwaltungSeiten", "website.verwaltung.seiten.suchen", new UI\Icon(Website\Icons::SEITE), "Bezeichnung", "Pfad", "Status");
$tabelle->setSeiten($tanfrage);

while($anfrage->werte($id, $art, $bezeichnung, $status, $pfad, $hatZugehoerig, $istStartseite)) {
  $zeile = new UI\Tabelle\Zeile($id);

  // Einrückung & Pfad bestimmen
  $einrueckung = 0;
  $pfadpre     = "";
  $zug = $id;
  while($DBS->anfrage("SELECT ws.id, {wsd.pfad} FROM website_seiten as ws JOIN website_seitendaten as wsd ON wsd.seite = ws.id WHERE id = (SELECT zugehoerig FROM website_seiten WHERE id = ?) AND wsd.sprache = (SELECT id FROM website_sprachen WHERE a2 = [?])", "is", $zug, $sprache)->werte($zid, $p)) {
    $zug      = $zid;
    $pfadpre .= "$p/";
    $einrueckung++;
  }
  if($einrueckung > 0) {
    $einrueckung = "<span style=\"padding-left: ".($einrueckung*20)."px\">".new UI\Icon("fas fa-long-arrow-alt-right")." ";
  } else {
    $einrueckung = "<span>";
  }
  $zeile["Bezeichnung"] = "$einrueckung$bezeichnung</span>";

  $zeile["Pfad"]        = "/$pfadpre$pfad";

  switch($status) {
    case "i":
      if($DSH_BENUTZER->hatRecht("website.seiten.bearbeiten")) {
        $kstatus = new UI\Knopf("Inaktiv", "Warnung", "website.verwaltung.seiten.setzen.status($id, 'a')");
      } else {
        $kstatus = new UI\Badge("Inaktiv", "Warnung");
      }
      break;
    case "a":
      if($DSH_BENUTZER->hatRecht("website.seiten.bearbeiten")) {
        $kstatus = new UI\Knopf("Aktiv", "Erfolg", "website.verwaltung.seiten.setzen.status($id, 'i')");
      } else {
        $kstatus = new UI\Badge("Aktiv", "Erfolg");
      }
      break;
  }
  $zeile["Status"] = $kstatus;

  if($istStartseite) {
    $zeile->setIcon(new UI\Icon("fas fa-home"));
  }

  if(!$istStartseite && $art == "i" && $status == "a" && $DSH_BENUTZER->hatRecht("website.seiten.startseite")) {
    $knopf = new UI\MiniIconKnopf(new UI\Icon("fas fa-home"), "Zur Startseite machen", "Erfolg");
    $knopf ->addFunktion("onclick", "website.verwaltung.seiten.startseite.fragen($id)");
    $zeile ->addAktion($knopf);
  }
  if($art == "m" && $DSH_BENUTZER->hatRecht("website.seiten.anlegen")) {
    $knopf = new UI\MiniIconKnopf(new UI\Icon("fas fa-long-arrow-alt-right"), "Unterseite anlegen", "Erfolg");
    $knopf ->addFunktion("onclick", "website.verwaltung.seiten.neu.fenster($id)");
    $zeile ->addAktion($knopf);
  }
  if($DSH_BENUTZER->hatRecht("website.seiten.bearbeiten")) {
    $knopf = new UI\MiniIconKnopf(new UI\Icon(UI\Konstanten::BEARBEITEN), "Seite bearbeiten");
    $knopf ->addFunktion("onclick", "website.verwaltung.seiten.bearbeiten.fenster($id)");
    $zeile ->addAktion($knopf);
  }
  if($DSH_BENUTZER->hatRecht("website.seiten.löschen")) {
    $knopf = new UI\MiniIconKnopf(new UI\Icon(UI\Konstanten::LOESCHEN), "Seite löschen", "Warnung");
    $knopf ->addFunktion("onclick", "website.verwaltung.seiten.loeschen.fragen($id)");
    $zeile ->addAktion($knopf);
  }

  $tabelle[] = $zeile;
}

Anfrage::setRueck("Code", (string) $tabelle);
?>