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
    global $DBS, $DSH_URL, $DSH_BENUTZER, $WEBSITE_URL, $versionen, $modi, $startseite, $WEBSITE_URL, $DSH_STANDARDSPRACHE, $DSH_SEITENPFAD, $standardversion, $standardmodus, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS;
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

          $brotkrumen = array_merge($brotkrumen, array("$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][$mod]}/$pfad$seg" => "$seg$klammer"));
        } else {
          $brotkrumen = array_merge($brotkrumen, array("$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][$mod]}/$pfad$seg" => $seg));
          $pfad .= "$seg/";
        }
      }

      $code .= (new Kern\Aktionszeile())->setBrotkrumenPfad($brotkrumen);

      $DBS->anfrage("SELECT {titel}, {inhalt} FROM website_fehlermeldungen WHERE sprache = (SELECT id FROM website_sprachen WHERE a2 = [?]) AND fehler = ?", "ss", $DSH_SPRACHE, $this->fehler)
        ->werte($ftitel, $finhalt);

      $code .= UI\Zeile::standard(new UI\Meldung("$ftitel", "$finhalt", "Fehler"));
      return $code;
    }
    // Kein Fehler -> Brotkrumen aus Bezeichnungen + Pfaden generieren, Titel aus Bezeichnung

    if(Kern\Check::angemeldet() && $DSH_BENUTZER->hatRecht("website.inhalte.versionen.[|alt,neu].[|sehen,aktivieren] || website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
      $DBS->anfrage("SELECT status FROM website_seiten WHERE id = ?", "i", $this->seitenId)
            ->werte($status);

      // Website bearbeiten
      $spalte = new UI\Spalte("A1");
      if($status == "i") {
        $spalte[] = new UI\Meldung("Inaktiv", "Die Seite ist inaktiv. Sie kann nicht /* @TODO: Meldung */", "Warnung", new UI\Icon("fas fa-eye-slash"));
      }
      $spalte ->addKlasse("dshWebsiteBearbeitenSpalte");

      $balken = new UI\Balken("Zeit", time(), $DSH_BENUTZER->getSessiontimeout(), false, $DSH_BENUTZER->getInaktivitaetszeit());
      $balken ->setID("dshWebsiteBearbeitenAktivitaet");
      $spalte[] = $balken;
      $spalte[] = "<script>kern.schulhof.nutzerkonto.aktivitaetsanzeige.hinzufuegen('dshWebsiteBearbeitenAktivitaet');</script>";
      $pfad     = join("/", $DSH_SEITENPFAD);

      // Variablen setzen (Falls Recht nicht vergeben)
      $knopfSehen = $knopfBearbeiten = $knopfAlt = $knopfAktuell = $knopfNeu = $knopfSeiteVersion = $knopfSeiteStatus = $knopfSeiteBearbeiten = $knopfSeiteLoeschen = null;

      $pfversion = "";
      if($ver !== $standardversion) {
        $pfversion = "{$versionen[$DSH_SPRACHE][$ver]}/";
      }
      $pfsprache = "";
      if($pfversion != "" || $DSH_SPRACHE !== $DSH_STANDARDSPRACHE) {
        $pfsprache = "$DSH_SPRACHE/";
      }

      $knopfSehen           = new UI\GrossIconKnopf(new UI\Icon("fas fa-binoculars"), "Betrachten");
      $knopfSehen           ->addFunktion("href", "$pfsprache$pfversion$pfad");
      if($DSH_BENUTZER->hatRecht("website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
        $knopfBearbeiten      = new UI\GrossIconKnopf(new UI\Icon(UI\Konstanten::BEARBEITEN), "Bearbeiten");
        $knopfBearbeiten      ->addFunktion("href", "$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][1]}/$pfad");
      }
      switch($DSH_SEITENMODUS) {
        case "sehen":
          $knopfSehen       ->addKlasse("dshUiKnopfErfolg");
          break;
        case "bearbeiten":
          $knopfBearbeiten  ->addKlasse("dshUiKnopfErfolg");
          break;
      }

      $pfmodus = "";
      if($mod !== $standardmodus) {
        $pfmodus = "{$modi[$DSH_SPRACHE][$mod]}/";
      }

      if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.alt.sehen")) {
        $knopfAlt         = new UI\GrossIconKnopf(new UI\Icon("fas fa-hourglass-end"),   "Alte Daten");
        $knopfAlt         ->addFunktion("href", "$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][0]}/$pfmodus$pfad");
      }
      $knopfAktuell     = new UI\GrossIconKnopf(new UI\Icon("fas fa-hourglass-half"),  "Aktuelle Daten");
      $knopfAktuell     ->addFunktion("href", "$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][1]}/$pfmodus$pfad");
      if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.sehen")) {
        $knopfNeu         = new UI\GrossIconKnopf(new UI\Icon("fas fa-hourglass-start"), "Neue Daten");
        $knopfNeu         ->addFunktion("href", "$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][2]}/$pfmodus$pfad");
      }
      switch($DSH_SEITENVERSION) {
        case "alt":
          $knopfAlt     ->addKlasse("dshUiKnopfErfolg");
          break;
        case "aktuell":
          $knopfAktuell ->addKlasse("dshUiKnopfErfolg");
          break;
        case "neu":
          $knopfNeu     ->addKlasse("dshUiKnopfErfolg");
          break;
      }

      $knopfSeiteVersion = null;
      if($DSH_SEITENVERSION == "alt" && $DSH_BENUTZER->hatRecht("website.inhalte.versionen.alt.aktivieren")) {
        $knopfSeiteVersion = new UI\GrossIconKnopf(new UI\Icon("fas fa-history fa-flip-horizontal"), "Daten wiederherstellen", "Warnung");
        $knopfSeiteVersion ->addFunktion("onclick", "website.verwaltung.seiten.setzen.version.fragen({$this->seitenId}, 'a', '$DSH_SPRACHE')");
      }
      if($DSH_SEITENVERSION == "neu" && $DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.aktivieren")) {
        $knopfSeiteVersion = new UI\GrossIconKnopf(new UI\Icon("fas fa-check-double"), "Daten freigeben", "Erfolg");
        $knopfSeiteVersion ->addFunktion("onclick", "website.verwaltung.seiten.setzen.version.fragen({$this->seitenId}, 'n', '$DSH_SPRACHE')");
      }

      if($DSH_BENUTZER->hatRecht("website.seiten.bearbeiten")) {
        if($status == "i") {
          $knopfSeiteStatus = new UI\GrossIconKnopf(new UI\Icon("fas fa-toggle-off"), "Aktivieren", "Erfolg");
          $knopfSeiteStatus ->addFunktion("onclick", "website.verwaltung.seiten.setzen.status({$this->seitenId}, 'a').then(_ => core.neuladen())");
        } else {
          $knopfSeiteStatus = new UI\GrossIconKnopf(new UI\Icon("fas fa-toggle-on"), "Deaktivieren", "Warnung");
          $knopfSeiteStatus ->addFunktion("onclick", "website.verwaltung.seiten.setzen.status({$this->seitenId}, 'i').then(_ => core.neuladen())");
        }

        $knopfSeiteBearbeiten = new UI\GrossIconKnopf(new UI\Icon(UI\Konstanten::BEARBEITEN), "Seite bearbeiten");
        $knopfSeiteBearbeiten ->addFunktion("onclick", "website.verwaltung.seiten.bearbeiten.fenster({$this->seitenId})");
      }
      if($DSH_BENUTZER->hatRecht("website.seiten.löschen")) {
        $knopfSeiteLoeschen   = new UI\GrossIconKnopf(new UI\Icon(UI\Konstanten::LOESCHEN), "Seite löschen", "Fehler");
        $knopfSeiteLoeschen   ->addFunktion("onclick", "website.verwaltung.seiten.loeschen.fragen({$this->seitenId})");
      }

      $aktionenModus      = (new UI\Box(new UI\Ueberschrift("3", "Modus"), $knopfSehen, $knopfBearbeiten))->addKlasse("modus");
      $aktionenVersion    = (new UI\Box(new UI\Ueberschrift("3", "Version"), $knopfAlt, $knopfAktuell, $knopfNeu))->addKlasse("version");
      $aktionenAktionen   = (new UI\Box(new UI\Ueberschrift("3", "Aktionen"), $knopfSeiteVersion, $knopfSeiteStatus, $knopfSeiteBearbeiten, $knopfSeiteLoeschen))->addKlasse("aktionen");

      $aktionen = new UI\Box();
      if(count($aktionenModus->getKinder()) > 1) {
        $aktionen[] = $aktionenModus;
      }
      if(count($aktionenVersion->getKinder()) > 1) {
        $aktionen[] = $aktionenVersion;
      }
      if(count($aktionenAktionen->getKinder()) > 1) {
        $aktionen[] = $aktionenAktionen;
      }
      $aktionen->setID("dshWebsiteBearbeitenAktionen");
      if(count($aktionen->getKinder()) > 0) {
        $spalte[] = $aktionen;
      }
      $code .= new UI\Zeile($spalte);
    }

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
        $DBS->anfrage("SELECT status FROM website_seiten WHERE id = ?", "i", $this->seitenId)
              ->werte($status);
        if($status == "i") {
          $extra[] = "Inaktiv";
        }
        $klammer = "";
        if(count($extra) > 0) {
          $klammer = " (".join(", ", $extra).")";
        }

        $this->titel = $bez;

        $brotkrumen = array_merge($brotkrumen, array("$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][$mod]}/$pfad$pf" => "$bez$klammer"));
      } else {
        $brotkrumen = array_merge($brotkrumen, array("$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$ver]}/{$modi[$DSH_SPRACHE][$mod]}/$pfad$pf" => $bez));
        $pfad .= "$pf/";
      }

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
      $sql[] = "SELECT '$el' as typ, el.id as id, el.position as position, el.status as status FROM website_$el as el WHERE el.seite = ?";
    }
    $sqlS = join("UNION", $sql);

    $inhalte = $DBS->anfrage("SELECT * FROM ($sqlS) AS x ORDER BY position ASC", str_repeat("i", count($sql)), array_fill(0, count($sql), $this->seitenId));
    $spalte  = new UI\Spalte("A1");
    while($inhalte->werte($typ, $id, $position, $status)) {
      // Elemente ausgeben
      $element = new $elemente[$typ]($id, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS);
      if($element->anzeigen()) {
        if($DSH_SEITENMODUS == "bearbeiten") {
          $neu = new UI\Box();
          $neu ->addKlasse("dshWebsiteNeuBalken");
          $neu ->addFunktion("onclick", "$(this).toggleKlasse('dshWebsiteNeuSichtbar')");
          $knopfEditorNeu = new UI\GrossIconKnopf(new UI\Icon("fas fa-pencil-alt"), "Neuer Editor", "Standard");
          $knopfEditorNeu ->addFunktion("onclick", "website.elemente.neu.fenster('editoren', $position, {$this->seitenId})");
          $neuMenue = new UI\Box($knopfEditorNeu);

          $spalte[] = $neu;
          $spalte[] = $neuMenue;
          $element->addFunktion("onclick", "website.elemente.bearbeiten.fenster('$typ', $id, '$DSH_SPRACHE')");
          $element->addKlasse("dshWebsiteBearbeitbar");
        }
        if($status == "i") {
          if($DSH_SEITENMODUS == "bearbeiten" && $DSH_BENUTZER->hatRecht("website.inhalte.elemente.bearbeiten")) {
            $element  ->addKlasse("dshWebsiteBearbeitenInaktiv");
            $spalte[] = $element;
          }
        } else {
          $spalte[] = $element;
        }
      }
    }
    if($DSH_SEITENMODUS == "bearbeiten") {
      $neu = new UI\Box();
      $neu ->addKlasse("dshWebsiteNeuBalken");
      $neu ->addFunktion("onclick", "$(this).toggleKlasse('dshWebsiteNeuSichtbar')");
      $knopfEditorNeu = new UI\GrossIconKnopf(new UI\Icon("fas fa-pencil-alt"), "Neuer Editor", "Standard");
      $knopfEditorNeu ->addFunktion("onclick", "website.elemente.neu.fenster('editoren', ".($position+1).", {$this->seitenId})");
      $neuMenue = new UI\Box($knopfEditorNeu);

      $spalte[] = $neu;
      $spalte[] = $neuMenue;
    }
    $code .= new UI\Zeile($spalte);

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
      if($a2 != $DSH_STANDARDSPRACHE || count($DSH_URL) > count($DSH_SEITENPFAD) + 1) {
        $infos[] = $a2;
      }
      if(count($DSH_URL) >= count($DSH_SEITENPFAD) + 2) {
        $infos[] = $versionen[$a2][$ver];
      }
      if(count($DSH_URL) >= count($DSH_SEITENPFAD) + 3) {
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

    $code .= UI\Zeile::standard($sprachwahl);

    return $code;
  }

  public static function vonPfad($sprache, $pfad, $version, $modus) : \Kern\Seite {
    global $DBS, $DSH_BENUTZER;

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
    $sqlStatus = "";
    if(!Kern\Check::angemeldet() || !$DSH_BENUTZER->hatRecht("website.inhalte.versionen.[|alt,neu].[|sehen,aktivieren] || website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
      $sqlStatus = " AND status = 'a'";
    }

    if($seitenId !== null && !$DBS->existiert("website_seiten", "id = ?$sqlStatus", "i", $seitenId)) {
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