<?php
Anfrage::post("id", "art", "status", "sprachen");
if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!in_array($art, ["i", "m"])) {
  Anfrage::addFehler(-3, true);
}

if(!in_array($status, ["a", "i"])) {
  Anfrage::addFehler(-3, true);
}

$sprachen = json_decode($sprachen, true);
if($sprachen === null) {
  Anfrage::addFehler(-3, true);
}

foreach($sprachen as $sprache => $werte) {
  if(!$DBS->anfrage("SELECT we.wert = ws.a2 FROM website_einstellungen as we JOIN website_sprachen as ws WHERE ws.a2 = [?]", "s", $sprache)->werte($istStandard)) {
    Anfrage::addFehler(-3, true);
  }
  if($istStandard) {
    if(!UI\Check::istText($werte["bezeichnung"])) {
      Anfrage::addFehler(14);
    }
    if(!UI\Check::istText($werte["pfad"], 0)) {
      Anfrage::addFehler(15);
    }
  } else {
    if(!UI\Check::istText($werte["bezeichnung"], 0)) {
      Anfrage::addFehler(14);
    }
    if(!UI\Check::istText($werte["pfad"], 0)) {
      Anfrage::addFehler(15);
    }
  }
}

if(!UI\Check::istZahl($id)) {
  foreach($sprachen as $w) {
    if($DBS->existiert("website_seitendaten as wsd", "pfad = [?] AND (SELECT zugehoerig FROM website_seiten as ws WHERE ws.id = wsd.seite) IS NULL", "s", $w["pfad"])) {
      Anfrage::addFehler(16);
    }
  }
} else {
  foreach($sprachen as $w) {
    if($DBS->existiert("website_seitendaten as wsd", "pfad = [?] AND (SELECT zugehoerig FROM website_seiten as ws WHERE ws.id = wsd.seite) = ?", "si", $w["pfad"], $id)) {
      Anfrage::addFehler(16);
    }
  }
}

Anfrage::checkFehler();

if (!$DSH_BENUTZER->hatRecht("website.seiten.anlegen")) {
  Anfrage::addFehler(-4, true);
}

if(!UI\Check::istZahl($id)) {
  $sid = $DBS->neuerDatensatz("website_seiten", array("art" => "?", "zugehoerig" => "NULL", "status" => "?"), "ss", $art, $status);
} else {
  $sid = $DBS->neuerDatensatz("website_seiten", array("art" => "?", "zugehoerig" => "?", "status" => "?"), "sis", $art, $id, $status);
}
$daten = [];
foreach($sprachen as $s => $w) {
  if($w["bezeichnung"] != "" || $w["pfad"] != "") {
    $daten[] = [$sid, $s, $w["bezeichnung"], $w["pfad"]];
  }
}
$DBS->anfrage("INSERT INTO website_seitendaten (seite, sprache, bezeichnung, pfad) VALUES (?, (SELECT id FROM website_sprachen WHERE a2 = [?]), [?], [?])", "isss", $daten);
$DBS->anfrage("UPDATE website_seitendaten SET bezeichnung = NULL WHERE bezeichnung = ['']");
$DBS->anfrage("UPDATE website_seitendaten SET pfad = NULL WHERE pfad = ['']");
?>