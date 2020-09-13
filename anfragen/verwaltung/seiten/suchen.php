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

$spalten = [["ws.id as id"], ["ws.art as art"], ["ws.status as status"], ["{(SELECT COALESCE(wsd.bezeichnung, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))))} as bezeichnung"], ["wsd.bezeichnung IS NULL as bezeichnungIstStandard"], ["{(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))))} as pfad"], ["wsd.pfad IS NULL AND wsd.bezeichnung IS NULL as pfadIstStandard"], ["ws.startseite as startseite"]];

$sql = "SELECT ?? FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.a2 = [?]";

$ta = new Kern\Tabellenanfrage($sql, $spalten, $sortSeite, $sortDatenproseite, $sortSpalte, $sortRichtung);
$tanfrage = $ta->anfrage($DBS, "s", $sprache);
$anfrage = $tanfrage["Anfrage"];

$tabelle = new UI\Tabelle("dshVerwaltungSeiten", "website.verwaltung.seiten.suchen", new UI\Icon("fas fa-edit"), "Bezeichnung", "Pfad", "Status");
$tabelle->setSeiten($tanfrage);

while($anfrage->werte($id, $art, $status, $bezeichnung, $bezeichnungIstStandard, $pfad, $pfadIstStandard, $istStartseite)) {
  $zeile = new UI\Tabelle\Zeile($id);

  // Einrückung & Pfad bestimmen
  $einrueckung = 0;
  $pfadpre     = "";
  $zug = $id;
  while($DBS->anfrage("SELECT ws.id, {(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))))}, wsd.pfad IS NULL FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.a2 = [?] AND ws.id = (SELECT zugehoerig FROM website_seiten WHERE id = ?)", "si", $sprache, $zug)->werte($zid, $p, $pIstStandard)) {
    $zug      = $zid;
    if($pIstStandard) {
      $pfadpre = "<i>$p</i>/$pfadpre";
    } else {
      $pfadpre = "$p/$pfadpre";
    }
    $einrueckung++;
  }
  if($einrueckung > 0) {
    $einrueckung = "<span style=\"padding-left: ".($einrueckung*20)."px\">".new UI\Icon("fas fa-long-arrow-alt-right")." ";
  } else {
    $einrueckung = "<span>";
  }
  if($bezeichnungIstStandard) {
    $zeile["Bezeichnung"] = "$einrueckung<i>$bezeichnung</i></span>";
  } else {
    $zeile["Bezeichnung"] = "$einrueckung$bezeichnung</span>";
  }
  if($pfadIstStandard) {
    $zeile["Pfad"]        = "/$pfadpre<i>$pfad</i>";
  } else {
    $zeile["Pfad"]        = "/$pfadpre$pfad";
  }

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

  if($art == "m") {
    $zeile->setIcon(new UI\Icon("fas fa-caret-right"));
  }

  if(!$istStartseite && $art == "i" && $status == "a" && $pfadpre == "" && $DSH_BENUTZER->hatRecht("website.seiten.startseite")) {
    $knopf = new UI\MiniIconKnopf(new UI\Icon("fas fa-home"), "Zur Startseite machen", "Erfolg");
    $knopf ->addFunktion("onclick", "website.verwaltung.seiten.startseite.fragen($id)");
    $zeile ->addAktion($knopf);
  }
  if($DSH_BENUTZER->hatRecht("website.seiten.anlegen")) {
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

  if($istStartseite) {
    $zeile->setIcon(new UI\Icon("fas fa-home"));
  }
  $tabelle[] = $zeile;
}

Anfrage::setRueck("Code", (string) $tabelle);
?>