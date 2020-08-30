<?php
namespace Website;
use Kern;
use UI;

class Seite extends Kern\Seite {
  /** @var integer */
  private $seitenId;
  /** @var string|null
   * Wenn <code>false</code>: Kein Fehler
   * Wenn <code>string</code>: Der Fehlercode
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

    $ver = array_search($DSH_SEITENVERSION, ["alt", "aktuell", "neu"]);
    $mod = array_search($DSH_SEITENMODUS, ["sehen", "bearbeiten"]);

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
          if($ver !== $standardversion) {
            $extra[] = $versionen[$DSH_SPRACHE][$ver];
          }
          if($mod !== $standardmodus) {
            $extra[] = $modi[$DSH_SPRACHE][$mod];
          }
          $klammer = "";
          if(count($extra) > 0) {
            $klammer = " (".join(", ", $extra).")";
          }

          $brotkrumen = array_merge($brotkrumen, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][$mod]}/$pfad$seg" => "$seg$klammer"));
        } else {
          $brotkrumen = array_merge($brotkrumen, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][$mod]}/$pfad$seg" => $seg));
          $pfad .= "$seg/";
        }
      }

      $code .= (new Kern\Aktionszeile())->setBrotkrumenPfad($brotkrumen);

      $DBS->anfrage("SELECT {titel}, {inhalt} FROM website_fehlermeldungen WHERE sprache = (SELECT id FROM website_sprachen WHERE a2 = [?]) AND fehler = ?", "ss", $DSH_SPRACHE, $this->fehler)
        ->werte($ftitel, $finhalt);

      $code .= UI\Zeile::standard(new UI\Meldung("$ftitel", "$finhalt", "Fehler"));
      return $code.$this->codedanach;
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
          if($DSH_SPRACHE !== $DSH_STANDARDSPRACHE) {
            $DBS->anfrage("SELECT {name} FROM website_sprachen WHERE a2 = [?]", "s", $DSH_SPRACHE)
                  ->werte($name);
            $extra[] = $name;
          }
          if($ver !== $standardversion) {
            $extra[] = $versionen[$DSH_SPRACHE][$ver];
          }
          if($mod !== $standardmodus) {
            $extra[] = $modi[$DSH_SPRACHE][$mod];
          }
          $klammer = "";
          if(count($extra) > 0) {
            $klammer = " (".join(", ", $extra).")";
          }

          $this->titel = $bez;

          $brotkrumen = array_merge($brotkrumen, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][$mod]}/$pfad$pf" => "$bez$klammer"));
        } else {
          $brotkrumen = array_merge($brotkrumen, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][$mod]}/$pfad$pf" => $bez));
          $pfad .= "$pf/";
        }
      }

      // Sprachwahl mit Pfaden
      $sprachwahl = new UI\Auswahl("dshWebsiteSprache", $DSH_SPRACHE);
      $sprachwahl ->addFunktion("oninput", "website.seite.aendern.sprache()");

      $anf = $DBS->anfrage("SELECT {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')), id as bezeichnung FROM website_sprachen");

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
          $infos[] = $versionen[$a2][$ver];
        }
        if(count($DSH_URL) > count($DSH_SEITENPFAD) + 2) {
          $infos[] = $modi[$a2][$mod];
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
    }

    $elemente = array();

    // Alle Elemente sammeln
    new Kern\Wurmloch("funktionen/website/elemente.php", array(), function($r) use (&$elemente) {
      $elemente = array_merge($elemente, $r);
    });

    $sql = [];
    $werte = [];
    foreach($elemente as $el => $c) {
      $sql[] = "SELECT '$el' as typ, el.id as id, el.position as position FROM website_$el as el WHERE el.seite = ?";
    }
    $sqlS = join("UNION", $sql);

    $inhalte = $DBS->anfrage("SELECT * FROM ($sqlS) AS x ORDER BY position ASC", str_repeat("i", count($sql)), array_fill(0, count($sql), $this->seitenId));
    $spalte  = new UI\Spalte("A1");
    while($inhalte->werte($typ, $id, $position)) {
      $spalte[] = new $elemente[$typ]($id, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS);
    }

    $code .= new UI\Zeile($spalte);
    $code .= UI\Zeile::standard($sprachwahl);
    return $code.$this->codedanach;
  }

  public static function vonPfad($sprache, $pfad, $version, $modus) : \Kern\Seite {
    global $DBS;

    // Pfad auflösen
    $DBS->anfrage("SELECT id, {fehler} FROM website_sprachen WHERE a2 = [?]", "s", $sprache)
          ->werte($sprachenId, $fehler);

    if($pfad[0] === $fehler) {
      // Fehlerseite
      return new Seite(-1, "404");
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