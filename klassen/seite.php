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
      $pfad = array(
       "Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$standardversion]}/{$modi[$DSH_SPRACHE][$standardmodus]}/{$startseite[$DSH_SPRACHE]}" => $startseite[$DSH_SPRACHE],
      );
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
          if(count($extra) > 0) {
            $p .= " (".join(", ", $extra).")";
          }

          $pfad = array_merge($pfad, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$DSH_SEITENVERSION]}/{$modi[$DSH_SPRACHE][$DSH_SEITENMODUS]}/$pf$p" => "$p"));
        } else {
          $pfad = array_merge($pfad, array("Website/$DSH_SPRACHE/{$versionen[$DSH_SPRACHE][$standardversion]}/{$modi[$DSH_SPRACHE][$standardmodus]}/$pf$p" => $p));
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

  public static function vonPfad($sprache, $pfad, $version, $modus) : Seite {

  }
}

?>