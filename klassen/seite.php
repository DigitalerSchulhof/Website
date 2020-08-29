<?php
namespace Website;
use Kern;

class Seite extends Kern\Seite {
  /** @var integer */
  private $seitenId;
  /** @var string|null
   * Wenn <code>false</code>: Kein Fehler
   * Wenn <code>string</code>: Der Titel
   */
  private $fehler;

  public function __construct($seitenId, $fehler = false) {
    parent::__construct(null, false, true);
    $this->seitenId = $seitenId;
    $this->fehler  = $fehler;
  }

  public function __toString() : string {
    global $DBS, $DSH_URL, $WEBSITE_URL, $versionen, $modi, $startseite, $WEBSITE_URL, $DSH_STANDARDSPRACHE, $DSH_SEITENPFAD, $standardversion, $standardmodus, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS;
    $code = "";

    // Brotkrumen
    $brotkrumen = [];
    if($this->fehler !== false) {
      // Fehlerseite -> Brotkrumen aus Pfad, Titel auf Fehler
      $this->titel = $this->fehler;

      $pfad = "";
      foreach($DSH_SEITENPFAD as $i => $seg) {
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

          $brotkrumen = array_merge($brotkrumen, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$DSH_SEITENVERSION]}/{$modi[$DSH_SPRACHE][$DSH_SEITENMODUS]}/$pfad$seg" => "$seg$klammer"));
        } else {
          $brotkrumen = array_merge($brotkrumen, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$DSH_SEITENVERSION]}/{$modi[$DSH_SPRACHE][$DSH_SEITENMODUS]}/$pfad$seg" => $seg));
          $pfad .= "$seg/";
        }
      }

      $code .= (new Kern\Aktionszeile())->setBrotkrumenPfad($brotkrumen);
    } else {
      // Kein Fehler -> Brotkrumen aus Bezeichnungen + Pfaden generieren, Titel aus Bezeichnung
      $zug = $this->seitenId;
      $pfadBez = [];
      while($DBS->anfrage("SELECT ws.zugehoerig, {(SELECT IF(wsd.bezeichnung IS NULL, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.bezeichnung))}, {(SELECT IF(wsd.pfad IS NULL, (SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.pfad))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE ws.id = ? AND wsp.a2 = [?]", "is", $zug, $DSH_SPRACHE)->werte($zug, $segB, $segP)) {
        $pfadBez[] = [$segP, $segB];
      }

      $pfadBez = array_reverse($pfadBez);
      $pfad = "";
      foreach($pfadBez as $i => $seg) {
        $pf   = $seg[0];
        $bez  = $seg[1];
        if($i === count($pfadBez)-1) {
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

          $this->titel = $bez;

          $brotkrumen = array_merge($brotkrumen, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$DSH_SEITENVERSION]}/{$modi[$DSH_SPRACHE][$DSH_SEITENMODUS]}/$pfad$pf" => "$bez$klammer"));
        } else {
          $brotkrumen = array_merge($brotkrumen, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$DSH_SEITENVERSION]}/{$modi[$DSH_SPRACHE][$DSH_SEITENMODUS]}/$pfad$pf" => $bez));
          $pfad .= "$pf/";
        }
      }

      // Sprachwahl mit Pfaden
      $sprachwahl = new \UI\Auswahl("dshWebsiteSprache", $DSH_SPRACHE);
      $sprachwahl ->addFunktion("oninput", "website.seite.aendern.sprache()");

      $anf = $DBS->anfrage("SELECT {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')), id as bezeichnung FROM website_sprachen");

      // Versionsfeld
      switch($DSH_SEITENVERSION) {
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
      switch($DSH_SEITENMODUS) {
        case 0:
          $mf = "sehen";
          break;
        case 1:
          $mf = "bearbeiten";
          break;
      }


      while($anf->werte($a2, $bez, $sprachId)) {
        $url = "";
        $zug = $this->seitenId;
        // Pfad für die Sprache bestimmen
        while($DBS->anfrage("SELECT ws.id, {(SELECT IF(wsd.pfad IS NULL, (SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.pfad))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = ? AND ws.id = (SELECT zugehoerig FROM website_seiten WHERE id = ?)", "ii", $sprachId, $zug)->werte($zid, $u)) {
          $zug      = $zid;
          $url  = "$u/$url";
        }
        // Letze Bezeichnung bestimmen
        $DBS->anfrage("SELECT {(SELECT IF(wsd.pfad IS NULL, (SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.pfad))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = ? AND ws.id = ?", "ii", $sprachId, $this->seitenId)
              ->werte($u);
        $url .= $u;
        $infos = [];
        if($a2 != $DSH_STANDARDSPRACHE || count($DSH_URL) != count($DSH_SEITENPFAD) + 1) {
          $infos[] = $a2;
        }
        if(count($DSH_URL) > count($DSH_SEITENPFAD) + 1) {
          $DBS->anfrage("SELECT {{$vf}} FROM website_sprachen WHERE id = ?", "i", $sprachId)
                ->werte($v);
          $infos[] = $v;
        }
        if(count($DSH_URL) > count($DSH_SEITENPFAD) + 2) {
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

      $code .= (new Kern\Aktionszeile())->setBrotkrumenPfad($brotkrumen);
      $code .= \UI\Zeile::standard($sprachwahl);
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
      $seite    = new Seite(-1, "$ftitel");
      $seite[]  = \UI\Zeile::standard(new \UI\Meldung("$ftitel", "$finhalt", "Fehler"));
      return $seite;
    }


    $seitenId = null;
    $pfadTrav = $pfad;
    while(count($pfadTrav) > 0) {
      $seg = array_shift($pfadTrav);

      if($seitenId === null) {
        if(!$DBS->anfrage("SELECT ws.id FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON ws.id = wsd.seite AND wsd.sprache = wsp.id WHERE ws.zugehoerig IS NULL AND wsp.id = ? AND IF(wsd.pfad IS NULL, {(SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT wsp.id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))} = ?, wsd.pfad = [?])", "iss", $sprachenId, $seg, $seg)->werte($seitenId)) {
          $seitenId = null;
          break;
        }
      } else {
        if(!$DBS->anfrage("SELECT ws.id FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON ws.id = wsd.seite AND wsd.sprache = wsp.id WHERE ws.zugehoerig = ? AND wsp.id = ? AND IF(wsd.pfad IS NULL, {(SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT wsp.id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))} = ?, wsd.pfad = [?])", "iiss", $seitenId, $sprachenId, $seg, $seg)->werte($seitenId)) {
          $seitenId = null;
          break;
        }
      }
    }
    if($seitenId !== null && !$DBS->existiert("website_seiten", "id = ? AND status = 'a'", "i", $seitenId)) {
      $seitenId = null;
    }

    if($seitenId === null) {
      // Seite nicht gefunden
      return self::vonPfad($sprache, [$fehler, "404"], 1, 0);
    }

    // Seite ist gültig

    return new Seite($seitenId);
  }
}

?>