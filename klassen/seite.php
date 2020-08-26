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
      // Kommentar, da eventuell doch, aber eigentlich macht es mehr Sinn, wenn die Startseite nicht als Root für alle Seiten zählt
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
    global $DBS, $versionen, $modi, $startseite, $standardversion, $standardmodus, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS, $DSH_SEITENPFAD;

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
        $DBS->anfrage("SELECT id FROM website_seiten as ws JOIN website_seitendaten as wsd ON ws.id = wsd.seite WHERE wsd.pfad = [?] AND ws.zugehoerig IS NULL AND wsd.sprache = ? LIMIT 1", "si", $seg, $sprachenId)
              ->werte($seitenId);
      } else {
        $DBS->anfrage("SELECT id FROM website_seiten as ws JOIN website_seitendaten as wsd ON ws.id = wsd.seite WHERE wsd.pfad = [?] AND ws.zugehoerig = ? AND wsd.sprache = ? LIMIT 1", "sii", $seg, $seitenId, $sprachenId)
              ->werte($seitenId);
      }
    }
    if($seitenId === null) {
      // Seite nicht gefunden
      return self::vonPfad($sprache, [$fehler, "404"], 1, 0);
    }

    // Seite ist gültig
    // $seitenId hält die ID der Seite





    return new Seite(join("/", $pfad));
  }
}

?>