<?php
namespace Website;
use Kern;

class Seite extends Kern\Seite {
  public function __construct($titel) {
    parent::__construct($titel, false, true);
  }

  public function __toString() : string {
    global $WEBSITE_URL;
    $code = "";
    if ($this->aktionszeile) {
      global $versionen, $modi, $startseite, $standardversion, $standardmodus, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS, $DSH_SEITENPFAD;
      $pfad = [];
      // Kommentar, da eventuell doch einebaut, aber eigentlich macht es mehr Sinn, wenn die Startseite nicht als Root für alle Seiten zählt und das hier wegbleibt
      // if($DSH_SEITENPFAD[0] != $startseite[$DSH_SPRACHE]) {
      //   $pfad = array(
      //     "Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$standardversion]}/{$modi[$DSH_SPRACHE][$standardmodus]}/{$startseite[$DSH_SPRACHE]}" => $startseite[$DSH_SPRACHE],
      //   );
      // }
      $pf = "";
      foreach($DSH_SEITENPFAD as $i => $p) {
        if($i === count($DSH_SEITENPFAD)-1) {
          // Letzte Seite

          $extra = [];
          if($DSH_SEITENVERSION !== $standardversion) {
            $extra[] = $versionen[$DSH_SPRACHE][$DSH_SEITENVERSION];
          }
          if($DSH_SEITENMODUS !== $standardmodus) {
            $extra[] = $modi[$DSH_SPRACHE][$DSH_SEITENMODUS];
          }
          $klammer = "";
          if(count($extra) > 0) {
            $klammer = " (".join(", ", $extra).")";
          }

          $pfad = array_merge($pfad, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$DSH_SEITENVERSION]}/{$modi[$DSH_SPRACHE][$DSH_SEITENMODUS]}/$pf$p" => "$p$klammer"));
        } else {
          $pfad = array_merge($pfad, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$DSH_SEITENVERSION]}/{$modi[$DSH_SPRACHE][$DSH_SEITENMODUS]}/$pf$p" => $p));
          $pf .= "$p/";
        }
      }
      $code .= (new Kern\Aktionszeile())->setBrotkrumenPfad($pfad);
    }
    foreach ($this->zeilen as $z) {
      $code .= $z;
    }
    return $code.$this->codedanach;
  }

  public static function vonPfad($sprache, $pfad, $version, $modus) : \Kern\Seite {
    global $DBS, $DSH_URL, $DSH_STANDARDSPRACHE, $versionen, $modi, $startseite, $standardversion, $standardmodus, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS, $DSH_SEITENPFAD;

    // Pfad auflösen
    $DBS->anfrage("SELECT id, {fehler} FROM website_sprachen WHERE a2 = [?]", "s", $sprache)
          ->werte($sprachenId, $fehler);

    if($pfad[0] === $fehler) {
      // Fehlerseite
      $DBS->anfrage("SELECT {titel}, {inhalt} FROM website_fehlermeldungen WHERE sprache = ? AND fehler = '404' LIMIT 1", "i", $sprachenId)
            ->werte($ftitel, $finhalt);
      $seite    = new Seite("$ftitel");
      $seite[]  = \UI\Zeile::standard(new \UI\Meldung("$ftitel", "$finhalt", "Fehler"));
      return $seite;
    }


    $seitenId = null;
    $pfadTrav = $pfad;
    while(count($pfadTrav) > 0) {
      $seg = array_shift($pfadTrav);

      if($seitenId === null) {
        if(!$DBS->anfrage("SELECT ws.id FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON ws.id = wsd.seite AND wsd.sprache = wsp.id WHERE ws.zugehoerig IS NULL AND wsp.id = ? AND ws.status = 'a' AND IF(wsd.pfad IS NULL, {(SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT wsp.id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))} = ?, wsd.pfad = [?])", "iss", $sprachenId, $seg, $seg)->werte($seitenId)) {
          $seitenId = null;
          break;
        }
      } else {
        if(!$DBS->anfrage("SELECT ws.id FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON ws.id = wsd.seite AND wsd.sprache = wsp.id WHERE ws.zugehoerig = ? AND wsp.id = ? AND ws.status = 'a' AND IF(wsd.pfad IS NULL, {(SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT wsp.id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))} = ?, wsd.pfad = [?])", "iiss", $seitenId, $sprachenId, $seg, $seg)->werte($seitenId)) {
          $seitenId = null;
          break;
        }
      }
    }

    if($seitenId === null) {
      // Seite nicht gefunden
      return self::vonPfad($sprache, [$fehler, "404"], 1, 0);
    }

    // Seite ist gültig
    // $seitenId hält die ID der Seite

    $DBS->anfrage("SELECT {(SELECT IF(wsd.bezeichnung IS NULL, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.bezeichnung))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = ? AND ws.id = ?", "ii", $sprachenId, $seitenId)
          ->werte($titel);

    $seite    = new Seite($titel);
    $seite[]  = \UI\Zeile::standard(new \UI\InhaltElement($seitenId));


    // Sprachwahl mit Pfaden
    $sprachwahl = new \UI\Auswahl("dshWebsiteSprache", $DSH_SPRACHE);
    $sprachwahl ->setTitel("Anzeigesprache");
    $sprachwahl ->addFunktion("oninput", "website.seite.aendern.sprache()");

    $anf = $DBS->anfrage("SELECT {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')), id as bezeichnung FROM website_sprachen");

    // Versionsfeld
    switch($version) {
      case 0:
        $vf = "alt";
        break;
      case 1:
        $vf = "aktuell";
        break;
      case 2:
        $vf = "neu";
        break;
    }

    // Modusfeld
    switch($modus) {
      case 0:
        $mf = "sehen";
        break;
      case 1:
        $mf = "bearbeiten";
        break;
    }


    while($anf->werte($a2, $bez, $sprachId)) {
      $url = "";
      $zug = $seitenId;
      // Pfad für die Sprache bestimmen
      while($DBS->anfrage("SELECT ws.id, {(SELECT IF(wsd.pfad IS NULL, (SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.pfad))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = ? AND ws.id = (SELECT zugehoerig FROM website_seiten WHERE id = ?)", "ii", $sprachId, $zug)->werte($zid, $u)) {
        $zug      = $zid;
        $url  = "$u/$url";
      }
      $DBS->anfrage("SELECT {(SELECT IF(wsd.pfad IS NULL, (SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.pfad))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = ? AND ws.id = ?", "ii", $sprachId, $seitenId)
            ->werte($u);
      $url .= $u;

      $infos = [];
      if($a2 != $DSH_STANDARDSPRACHE || count($DSH_URL) != count($pfad) + 1) {
        $infos[] = $a2;
      }
      if(count($DSH_URL) > count($pfad) + 1) {
        $DBS->anfrage("SELECT {{$vf}} FROM website_sprachen WHERE id = ?", "i", $sprachId)
              ->werte($v);
        $infos[] = $v;
      }
      if(count($DSH_URL) > count($pfad) + 2) {
        $DBS->anfrage("SELECT {{$mf}} FROM website_sprachen WHERE id = ?", "i", $sprachId)
              ->werte($v);
        $infos[] = $v;
      }
      $infos = join("/", $infos);
      if(strlen($infos) > 0) {
        $infos .= "/";
      }
      $sprachwahl->add($bez, "$infos$url", $DSH_SPRACHE == $a2);
    }
    $sprachwahl->addKlasse("dshUiEingabefeldKlein");
    $sprachwahl->setStyle("float", "right");

    $seite[] = \UI\Zeile::standard($sprachwahl);

    return $seite;
  }
}

?>