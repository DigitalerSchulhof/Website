<?php
namespace Website;
use Kern;
use UI;

class Seite extends Kern\Seite {
  /** @var array Daten der Seite
   * ["id"        => ?int]      ID der Seite
   * ["sprache"   => string]    A2-Kürzel der Sprache
   * ["modus"     => string]    Betrachtungsmodus ["sehen", "beaebeiten"]
   * ["version"   => string]    Version der Seite ["alt", "aktuell", "neu"]
   * ["pfad"      => string[]]  Aufgerufene Seite
   *
   * ["fehler"    => string]    Fehlercode - Gesetzt, wenn <code>id === null</code>
   */
  private $seite;

  /**
   * Lädt die Seite
   * @param array $daten Seitendaten (Siehe: Kern\Seite::$seite)
   */
  public function __construct($daten) {
    global $DBS;
    parent::__construct(null, false, true);
    $this->seite = $daten;
    $DBS->anfrage("SELECT id FROM website_sprachen WHERE a2 = [?]", "s", $this->seite["sprache"])
          ->werte($this->seite["spracheI"]);

    if($this->seite["id"] !== null) {
      // Daten laden
      $DBS->anfrage("SELECT status, art FROM website_seiten WHERE id = ?", "i", $this->seite["id"])
            ->werte($this->seite["status"], $this->seite["art"]);
    }
  }

  /**
   * Erzeugt aus Seitenmetadaten einen String, welcher vor den Seitenpfad gehängt werden kann
   * @param  string|null $sprache Wenn <code>null</code>: Aktuelle Sprache
   * @param  string|null $version Wenn <code>null</code>: Aktuelle Version in der gewählten Sprache
   * @param  string|null $modus   Wenn <code>null</code>: Aktueller Modus in der gewählten Sprache
   * @return string
   */
  public function meta($sprache = null, $version = null, $modus = null) {
    global $DSH_SEITENVERSIONEN, $DSH_SEITENMODI;
    $sprache  = $sprache  ?? $this->seite["sprache"];
    $version  = $version  ?? $this->seite["version"];
    $modus    = $modus    ?? $this->seite["modus"];

    // (Ausgabe)
    $versionA = $DSH_SEITENVERSIONEN[$sprache][$version];
    $modusA   = $DSH_SEITENMODI[$sprache][$modus];
    if($modus   != STANDARDMODUS) {
      return "$sprache/$versionA/$modusA";
    }
    if($version != STANDARDVERSION) {
      return "$sprache/$versionA";
    }
    if($sprache != STANDARDSPRACHE) {
      return "$sprache";
    }
    return "";
  }

  /**
   * Erzeugt eine Bearbeiten-Spalte
   * @return UI\Spalte
   */
  public function genBearbeiten() : UI\Spalte {
    global $DSH_BENUTZER;
    $spalte = new UI\Spalte("A1");
    if($this->seite["status"] == "i") {
      $spalte[] = new UI\Meldung("Inaktiv", "Die Seite ist inaktiv. Sie kann nicht /* @TODO: Meldung */", "Warnung", new UI\Icon("fas fa-eye-slash"));
    }
    $spalte ->addKlasse("dshWebsiteBearbeitenSpalte");

    $balken = new UI\Balken("Zeit", time(), $DSH_BENUTZER->getSessiontimeout(), false, $DSH_BENUTZER->getInaktivitaetszeit());
    $balken ->setID("dshWebsiteBearbeitenAktivitaet");
    $spalte[] = $balken;
    $spalte[] = "<script>kern.schulhof.nutzerkonto.aktivitaetsanzeige.hinzufuegen('dshWebsiteBearbeitenAktivitaet');</script>";
    $pfad     = join("/", $this->seite["pfad"]);

    // Variablen setzen (Falls Recht nicht vergeben)
    $knopfSehen = $knopfBearbeiten = $knopfAlt = $knopfAktuell = $knopfNeu = $knopfSeiteVersion = $knopfSeiteStatus = $knopfSeiteBearbeiten = $knopfSeiteLoeschen = null;

    // MODUS
    $knopfSehen           = new UI\GrossIconKnopf(new UI\Icon("fas fa-binoculars"), "Betrachten");
    $knopfSehen           ->addFunktion("href", "{$this->meta(null, null, "sehen")}/$pfad");
    if($DSH_BENUTZER->hatRecht("website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
      $knopfBearbeiten      = new UI\GrossIconKnopf(new UI\Icon(UI\Konstanten::BEARBEITEN), "Bearbeiten");
      $knopfBearbeiten      ->addFunktion("href", "{$this->meta(null, null, "bearbeiten")}/$pfad");
    }

    // VERSION
    if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.alt.sehen")) {
      $knopfAlt         = new UI\GrossIconKnopf(new UI\Icon("fas fa-hourglass-end"),   "Alte Daten");
      $knopfAlt         ->addFunktion("href", "{$this->meta(null, "alt", null)}/$pfad");
    }
    $knopfAktuell     = new UI\GrossIconKnopf(new UI\Icon("fas fa-hourglass-half"),  "Aktuelle Daten");
    $knopfAktuell     ->addFunktion("href", "{$this->meta(null, "aktuell", null)}/$pfad");
    if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.sehen")) {
      $knopfNeu         = new UI\GrossIconKnopf(new UI\Icon("fas fa-hourglass-start"), "Neue Daten");
      $knopfNeu         ->addFunktion("href", "{$this->meta(null, "neu", null)}/$pfad");
    }

    // AKTIONEN
    $knopfSeiteVersion = null;
    if($this->seite["version"] == "alt" && $DSH_BENUTZER->hatRecht("website.inhalte.versionen.alt.aktivieren")) {
      $knopfSeiteVersion = new UI\GrossIconKnopf(new UI\Icon("fas fa-history fa-flip-horizontal"), "Daten wiederherstellen", "Warnung");
      $knopfSeiteVersion ->addFunktion("onclick", "website.verwaltung.seiten.setzen.version.fragen({$this->seite["id"]}, 'a', '{$this->seite["sprache"]}')");
    }
    if($this->seite["version"] == "neu" && $DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.aktivieren")) {
      $knopfSeiteVersion = new UI\GrossIconKnopf(new UI\Icon("fas fa-check-double"), "Daten aktivieren", "Erfolg");
      $knopfSeiteVersion ->addFunktion("onclick", "website.verwaltung.seiten.setzen.version.fragen({$this->seite["id"]}, 'n', '{$this->seite["sprache"]}')");
    }

    if($DSH_BENUTZER->hatRecht("website.seiten.bearbeiten")) {
      if($this->seite["status"] == "i") {
        $knopfSeiteStatus = new UI\GrossIconKnopf(new UI\Icon("fas fa-toggle-off"), "Aktivieren", "Erfolg");
        $knopfSeiteStatus ->addFunktion("onclick", "website.verwaltung.seiten.setzen.status({$this->seite["id"]}, 'a').then(_ => core.neuladen())");
      } else {
        $knopfSeiteStatus = new UI\GrossIconKnopf(new UI\Icon("fas fa-toggle-on"), "Deaktivieren", "Warnung");
        $knopfSeiteStatus ->addFunktion("onclick", "website.verwaltung.seiten.setzen.status({$this->seite["id"]}, 'i').then(_ => core.neuladen())");
      }

      $knopfSeiteBearbeiten = new UI\GrossIconKnopf(new UI\Icon(UI\Konstanten::BEARBEITEN), "Seite bearbeiten");
      $knopfSeiteBearbeiten ->addFunktion("onclick", "website.verwaltung.seiten.bearbeiten.fenster({$this->seite["id"]})");
    }
    if($DSH_BENUTZER->hatRecht("website.seiten.löschen")) {
      $knopfSeiteLoeschen   = new UI\GrossIconKnopf(new UI\Icon(UI\Konstanten::LOESCHEN), "Seite löschen", "Fehler");
      $knopfSeiteLoeschen   ->addFunktion("onclick", "website.verwaltung.seiten.loeschen.fragen({$this->seite["id"]})");
    }

    ${"knopf".ucfirst($this->seite["modus"])}->addKlasse("dshUiKnopfErfolg");
    ${"knopf".ucfirst($this->seite["version"])}->addKlasse("dshUiKnopfErfolg");
    $aktionenModus      = new UI\Box(new UI\Ueberschrift("3", "Modus"), $knopfSehen, $knopfBearbeiten);
    $aktionenModus      ->addKlasse("modus");
    $aktionenVersion    = new UI\Box(new UI\Ueberschrift("3", "Version"), $knopfAlt, $knopfAktuell, $knopfNeu);
    $aktionenVersion    ->addKlasse("version");
    $aktionenAktionen   = new UI\Box(new UI\Ueberschrift("3", "Aktionen"), $knopfSeiteVersion, $knopfSeiteStatus, $knopfSeiteBearbeiten, $knopfSeiteLoeschen);
    $aktionenAktionen   ->addKlasse("aktionen");

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
    return $spalte;
  }

  public function __toString() : string {
    global $DBS, $DSH_BENUTZER, $DSH_SEITENMODI, $DSH_SEITENVERSIONEN;
    $code = "";

    // Bearbeiten/Versionen Rechte-Check
    if($this->seite["id"] !== null) {
      if(in_array($this->seite["version"], ["alt", "neu"])) {
        if(!Kern\Check::angemeldet(false) || !$DSH_BENUTZER->hatRecht("website.inhalte.versionen.{$this->seite["version"]}.sehen")) {
          $this->seite["id"]      = null;
          $this->seite["fehler"]  = "403";
        }
      }

      if(in_array($this->seite["modus"], ["bearbeiten"])) {
        if(!Kern\Check::angemeldet(false) || !$DSH_BENUTZER->hatRecht("website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
          $this->seite["id"]      = null;
          $this->seite["fehler"]  = "403";
        }
      }
    }

    if($this->seite["id"] === null) {
      // Fehlerseite -> Brotkrumen aus Pfad, Titel auf Fehler
      $this->titel = $this->seite["fehler"];

      $brotkrumen = [];
      $pfad = "";
      foreach($this->seite["pfad"] as $i => $seg) {
        $pf  = $seg;
        $bez = Kern\Texttrafo::url2text($seg);
        $extra = "";
        if($i === count($this->seite["pfad"])-1) {
          // Letzte Seite
          $ex = [];
          if($this->seite["version"] !== STANDARDVERSION) {
            $ex[] = $DSH_SEITENVERSIONEN[$this->seite["sprache"]][$this->seite["version"]];
          }
          if($this->seite["modus"] !== STANDARDMODUS) {
            $ex[] = $DSH_SEITENMODI[$this->seite["sprache"]][$this->seite["modus"]];
          }
          if(count($ex) > 0) {
            $extra = " (".join(", ", $ex).")";
          }
        }
        $brotkrumen["{$this->meta()}/$pfad$pf"] = "$bez$extra";
        $pfad .= "$pf/";
      }

      $code .= (new Kern\Aktionszeile())->setBrotkrumenPfad($brotkrumen);

      /**
       * @var string $ftitel
       * @var string $finhalt
       */

      $DBS->anfrage("SELECT {COALESCE(titel, (SELECT titel FROM website_fehlermeldungen as wfms WHERE sprache = (SELECT id FROM website_sprachen WHERE a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)) AND wfms.fehler = wfm.fehler))}, {COALESCE(inhalt, (SELECT inhalt FROM website_fehlermeldungen as wfms WHERE sprache = (SELECT id FROM website_sprachen WHERE a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)) AND wfms.fehler = wfm.fehler))} FROM website_fehlermeldungen as wfm WHERE sprache = ? AND fehler = ?", "is", $this->seite["spracheI"], $this->seite["fehler"])
        ->werte($ftitel, $finhalt);

      $code .= UI\Zeile::standard(new UI\Meldung("$ftitel", "$finhalt", "Fehler"));
      return $code;
    }

    // Kein Fehler -> Brotkrumen aus Bezeichnungen + Pfaden generieren, Titel aus Bezeichnung

    if(Kern\Check::angemeldet() && $DSH_BENUTZER->hatRecht("website.inhalte.versionen.[|alt,neu].[|sehen,aktivieren] || website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
      // Website bearbeiten
      $code .= new UI\Zeile($this->genBearbeiten());
    }


    $zug = $this->seite["id"];
    $pfadBez = [];
    /**
     * @var string $segB
     * @var string $segP
     */
    while($DBS->anfrage("SELECT ws.zugehoerig, {(SELECT COALESCE(wsd.bezeichnung, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))))}, {(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE ws.id = ? AND wsp.a2 = [?]", "is", $zug, $this->seite["sprache"])->werte($zug, $segB, $segP)) {
      $pfadBez[] = [$segP, $segB];
    }
    $pfadBez = array_reverse($pfadBez);

    $pfad = "";
    foreach($pfadBez as $i => $seg) {
      $pf   = $seg[0];
      $bez  = $seg[1];
      $extra = "";
      if($i === count($pfadBez)-1) {
        // Letzte Seite
        $ex = [];
        if($this->seite["sprache"] != STANDARDSPRACHE) {
          /**
           * @var string $name
           */
          $DBS->anfrage("SELECT {name} FROM website_sprachen WHERE a2 = [?]", "s", $this->seite["sprache"])
                ->werte($name);
          $ex[] = Kern\Texttrafo::url2text($name);
        }
        if($this->seite["version"] != STANDARDVERSION) {
          $ex[] = Kern\Texttrafo::url2text($DSH_SEITENVERSIONEN[$this->seite["sprache"]][$this->seite["version"]]);
        }
        if($this->seite["modus"] != STANDARDMODUS) {
          $ex[] = Kern\Texttrafo::url2text($DSH_SEITENMODI[$this->seite["sprache"]][$this->seite["modus"]]);
        }

        if($this->seite["status"] == "i") {
          $extra[] = "Inaktiv";
        }
        if(count($ex) > 0) {
          $extra = " (".join(", ", $ex).")";
        }

        $this->titel = $bez;

      }
      $brotkrumen["{$this->meta()}/$pfad$pf"] = "$bez$extra";
      $pfad .= "$pf/";
    }
    $code .= (new Kern\Aktionszeile())->setBrotkrumenPfad($brotkrumen);

    $elemente = [];

    if($this->seite["art"] == "i") {
      // Alle Elemente sammeln
      new Kern\Wurmloch("funktionen/website/elemente.php", [], function($r) use (&$elemente) {
        $elemente = array_merge($elemente, $r);
      });

      $sql = [];
      foreach($elemente as $el => $c) {
        $sql[] = "SELECT '$el' as typ, el.id as id, el.position as position, el.status as status FROM website__$el as el WHERE el.seite = ? AND el.sprache = (SELECT id FROM website_sprachen WHERE a2 = [?])";
      }
      $sqlS = join("UNION", $sql);

      $inhalte = $DBS->anfrage("SELECT * FROM ($sqlS) AS x ORDER BY position ASC", str_repeat("is", count($sql)), array_fill(0, count($sql), [$this->seite["id"], $this->seite["sprache"]]));
      $spalte  = new UI\Spalte("A1");
      /**
       * @var string $typ
       * @var int $id
       * @var int $position
       * @var string $status
       */
      while($inhalte->werte($typ, $id, $position, $status)) {
        // Elemente ausgeben
        $element = new $elemente[$typ]($id, $this->seite["version"], $this->seite["modus"]);
        if($element->anzeigen()) {
          if($this->seite["modus"] == "bearbeiten") {
            $neu = new UI\Box();
            $neu ->addKlasse("dshWebsiteNeuBalken");
            $neu ->addFunktion("onclick", "$(this).toggleKlasse('dshWebsiteNeuSichtbar')");
            $knopfEditorNeu = new UI\GrossIconKnopf(new UI\Icon("fas fa-pencil-alt"), "Neuer Editor", "Standard");
            $knopfEditorNeu ->addFunktion("onclick", "website.elemente.neu.fenster('editoren', $position, {$this->seite["id"]}, '{$this->seite["sprache"]})");
            $neuMenue = new UI\Box($knopfEditorNeu);

            $spalte[] = $neu;
            $spalte[] = $neuMenue;
            $element->addFunktion("onclick", "website.elemente.bearbeiten.fenster('$typ', $id, '{$this->seite["sprache"]}')");
            $element->addKlasse("dshWebsiteBearbeitbar");
          }
          if($status == "i") {
            if($this->seite["modus"] == "bearbeiten" && $DSH_BENUTZER->hatRecht("website.inhalte.elemente.bearbeiten")) {
              $element  ->addKlasse("dshWebsiteBearbeitenInaktiv");
              $spalte[] = $element;
            }
          } else {
            $spalte[] = $element;
          }
        }
      }
      if($this->seite["modus"] == "bearbeiten") {
        $neu = new UI\Box();
        $neu ->addKlasse("dshWebsiteNeuBalken");
        $neu ->addFunktion("onclick", "$(this).toggleKlasse('dshWebsiteNeuSichtbar')");
        $knopfEditorNeu = new UI\GrossIconKnopf(new UI\Icon("fas fa-pencil-alt"), "Neuer Editor", "Standard");
        $knopfEditorNeu ->addFunktion("onclick", "website.elemente.neu.fenster('editoren', ".($position+1).", {$this->seite["id"]}, '{$this->seite["sprache"]}')");
        $neuMenue = new UI\Box($knopfEditorNeu);

        $spalte[] = $neu;
        $spalte[] = $neuMenue;
      }
      $code .= new UI\Zeile($spalte);
    } else {
      // Unternavigation laden
      $spalte = new UI\Spalte();
      if($this->seite["modus"] == "bearbeiten") {
        $spalte[] = new UI\Meldung("Automatischer Inhalt", "Der Inhalt dieser Seite wird automatisch aus den Unterseiten generiert.", "Information");
      }
      /**
       * @var string $titel
       */
      $DBS->anfrage("SELECT {(SELECT COALESCE(wsd.bezeichnung, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE ws.id = ? AND wsp.a2 = [?]", "is", $this->seite["id"], $this->seite["sprache"])
            ->werte($titel);
      $spalte[] = new UI\SeitenUeberschrift($titel);

      $sqlStatus = "";
      if (!Kern\Check::angemeldet() || !$DSH_BENUTZER->hatRecht("website.inhalte.versionen.[|alt,neu].[|sehen,aktivieren] || website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
        $sqlStatus = " AND status = 'a'";
      }

      $anf = $DBS->anfrage("SELECT ws.id, {(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))))}, {(SELECT COALESCE(wsd.bezeichnung, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = (SELECT id FROM website_sprachen WHERE a2 = [?]) AND ws.zugehoerig = ?$sqlStatus", "si", $this->seite["sprache"], $this->seite["id"]);
      /**
       * @var int $unterid
       */
      while ($anf->werte($unterid, $pf, $bez)) {
        if ((Kern\Check::angemeldet() && $DSH_BENUTZER->hatRecht("website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) || self::sichtbar($unterid, $this->seite["sprache"])) {
          $direkt  = new UI\IconKnopf(new UI\Icon("fas fa-globe"), $bez);
          $direkt->addFunktion("href", "{$this->meta()}".join("/", $this->seite["pfad"])."/$pf");
          $spalte[] = "$direkt ";
        }
      }

      if (count($spalte->getElemente()) == 1) { // 1 weil Titel
        $spalte[] = new UI\Notiz("Keine Unterseiten verfügbar");
        // @TODO: Übersetzung
      }

      $code .= new UI\Zeile($spalte);
    }

    $sprachwahl = new UI\Auswahl("dshWebsiteSprache", $this->seite["sprache"]);
    $sprachwahl ->addFunktion("oninput", "website.seite.aendern.sprache()");
    $anf = $DBS->anfrage("SELECT {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')), id as bezeichnung FROM website_sprachen");

    /**
     * @var string $a2
     * @var string $bez
     * @var int $spracheI
     */
    while($anf->werte($a2, $bez, $spracheI)) {
      if(!(Kern\Check::angemeldet() && $DSH_BENUTZER->hatRecht("website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) && !self::sichtbar($this->seite["id"], $a2, $this->seite["version"])) {
        continue;
      }
      $url = "";
      $zug = $this->seite["id"];
      // Pfad für die Sprache bestimmen
      /**
       * @var string $zid
       * @var string $u
       */
      while($DBS->anfrage("SELECT ws.id, {(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = ? AND ws.id = (SELECT zugehoerig FROM website_seiten WHERE id = ?)", "ii", $spracheI, $zug)->werte($zid, $u)) {
        $zug      = $zid;
        $url  = "$u/$url";
      }
      // Letze Bezeichnung bestimmen
      $DBS->anfrage("SELECT {(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = ? AND ws.id = ?", "ii", $spracheI, $this->seite["id"])
            ->werte($u);
      $url .= $u;
      $url = Kern\Texttrafo::text2url($url);
      $sprachwahl->add($bez, "{$this->meta($a2)}/$url", $this->seite["sprache"] == $a2);
    }
    $sprachwahl->addKlasse("dshUiEingabefeldKlein");
    $sprachwahl->setStyle("float", "right");
    if(count($sprachwahl->getOptionen()) > 1) {
      $code .= UI\Zeile::standard($sprachwahl);
    }

    return $code;
  }

  public static function vonPfad($sprache, $pfad, $version, $modus) : \Kern\Seite {
    global $DBS, $DSH_BENUTZER;

    $meta = [
      "sprache" => $sprache,
      "version" => $version,
      "modus"   => $modus,
      "pfad"    => $pfad
    ];

    // Pfad auflösen
    /**
     * @var int $spracheI
     */
    $DBS->anfrage("SELECT id FROM website_sprachen WHERE a2 = [?]", "s", $sprache)
          ->werte($spracheI);

    $seiteI = null;
    $pfadTrav = $pfad;
    while(count($pfadTrav) > 0) {
      $seg = array_shift($pfadTrav);
      $seg = Kern\Texttrafo::url2text($seg);
      if($seiteI === null) {
        if(!$DBS->anfrage("SELECT ws.id FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON ws.id = wsd.seite AND wsd.sprache = wsp.id WHERE ws.zugehoerig IS NULL AND wsp.id = ? AND IF(wsd.pfad IS NULL, {COALESCE(wsd.bezeichnung, (SELECT IF(wsds.pfad IS NULL, wsds.bezeichnung, wsds.pfad) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT wsp.id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))} = ?, wsd.pfad = [?])", "iss", $spracheI, $seg, $seg)->werte($seiteI)) {
          $seiteI = null;
          break;
        }
      } else {
        if(!$DBS->anfrage("SELECT ws.id FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON ws.id = wsd.seite AND wsd.sprache = wsp.id WHERE ws.zugehoerig = ? AND wsp.id = ? AND IF(wsd.pfad IS NULL, {COALESCE(wsd.bezeichnung, (SELECT IF(wsds.pfad IS NULL, wsds.bezeichnung, wsds.pfad) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT wsp.id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))} = ?, wsd.pfad = [?])", "iiss", $seiteI, $spracheI, $seg, $seg)->werte($seiteI)) {
          $seiteI = null;
          break;
        }
      }
    }
    $sqlStatus = "";
    if(!Kern\Check::angemeldet() || !$DSH_BENUTZER->hatRecht("website.inhalte.versionen.[|alt,neu].[|sehen,aktivieren] || website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
      $sqlStatus = " AND status = 'a'";
    }

    if($seiteI !== null && !$DBS->existiert("website_seiten", "id = ?$sqlStatus", "i", $seiteI)) {
      $seiteI = null;
    }

    if($seiteI === null || (!(Kern\Check::angemeldet() && $DSH_BENUTZER->hatRecht("website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) && !self::sichtbar($seiteI, $sprache, $version))) {
      // Seite nicht gefunden
      return new Seite(array_merge($meta, [
        "id"      => null,
        "fehler"  => "404"
      ]));
    }

    // Seite ist gültig
    return new Seite(array_merge($meta, [
      "id"      => $seiteI,
    ]));
  }

  /**
   * Gibt zurück, ob die Kombination aus Seite und Sprache sichtbar ist (Mind. ein Element mit Inhalt)
   *
   * @param int $id               Die ID der Seite
   * @param string $sprache       Die A2-Kennung der Sprache
   * @param string|null $version  Die Seitenversion
   * Wenn <code>null</code>: Standardversion
   * @return boolean
   */
  public static function sichtbar($id, $sprache, $version = null) : bool {
    global $DBS;
    $version ??= STANDARDVERSION;

    /**
     * @var string $art
     */
    $DBS->anfrage("SELECT art FROm website_seiten WHERE id = ?", "i", $id)
          ->werte($art);
    if($art == "m") {
      return true;
    }

    $elemente = [];
    // Alle Elemente sammeln
    new Kern\Wurmloch("funktionen/website/elemente.php", [], function ($r) use (&$elemente) {
      $elemente = array_merge($elemente, $r);
    });

    $sql = [];
    foreach ($elemente as $el => $c) {
      $sql[] = "SELECT '$el' as typ, el.id as id, el.position as position, el.status as status FROM website__$el as el WHERE el.seite = ? AND el.sprache = (SELECT id FROM website_sprachen WHERE a2 = [?])";
    }
    $sqlS = join("UNION", $sql);

    $inhalte = $DBS->anfrage("SELECT * FROM ($sqlS) AS x ORDER BY position ASC", str_repeat("is", count($sql)), array_fill(0, count($sql), [$id, $sprache]));
    $spalte  = new UI\Spalte("A1");
    /**
     * @var string $typ
     * @var int $id
     * @var int $position
     * @var string $status
     */
    while ($inhalte->werte($typ, $id, $position, $status)) {
      $elm = new $elemente[$typ]($id, $version, "sehen");
      if($elm->anzeigen()) {
        return true;
      }
    }

    return false;
  }
}

?>