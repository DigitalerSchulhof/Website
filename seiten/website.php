<?php

/*

  Ideale URL (RegEx): sei
  {z} : [\-\:\(\)\[\]\{\}äöüßáéíóúàèìòùæâêîôûøØÇãåçëïõÿñ0-9a-z]
  {s} : ((?:{z}+\/)*(?:{z}+)+)

  /^Website\/(DE|EN|FR)\/(Alt|Aktuell|Neu)\/(Sehen|Bearbeiten)\/{s}$/i
    => /^Website\/(DE|EN|FR)\/(Alt|Aktuell|Neu)\/(Sehen|Bearbeiten)\/((?:[\-\:\(\)\[\]\{\}äöüßáéíóúàèìòùæâêîôûøØÇãåçëïõÿñ0-9a-z]+\/)*(?:[\-\:\(\)\[\]\{\}äöüßáéíóúàèìòùæâêîôûøØÇãåçëïõÿñ0-9a-z]+)+)$/i

  Bei Sprache, Version und Modus sind die jeweils Vorherigen notwendig, um eine gültige URL zu bilden.

  Abkürzen ist insoweit möglich, als bei Sprache »DE«, bei Version »Aktuell«, und bei Modus »Sehen« angenommen wird, sofern dises nicht gegeben sind:


  Website/{s}                     -> Website/DE/Aktuell/Sehen/{s}
  Website/Sehen/{s}               -> Ungültig, da Sprache fehlt
  Website/Bearbeiten/{s}          -> Ungültig, da Sprache und Version fehlen
  Website/EN/{s}                  -> Website/EN/Current/View/{s}
  Website/DE/Aktuell/{s}          -> Website/DE/Aktuell/Sehen/{s}
  Website/FR/Ancien/{s}           -> Website/FR/Ancien/Voir/{s}
  Website/DE/Neu/Bearbeiten/{s}   -> Website/DE/Neu/Bearbeiten/{s}

  Ist Sprache, Version oder Modus gegeben, muss {s} angegeben sein.

  Beim Anlegen einer direkten Unterseite von / sind folgende Bezeichnungen nicht erlaubt:
  - DE | EN | FR | (Weitere Sprachkürzel)
  - [Alt | Aktuell | Neu] | [Old | Current | New] | [Ancien | Actuel | Neuveau] | (Weitere Versionen nach Sprache), je nach gewählter Sprache
  - [Sehen | Bearbeiten] | [View | Edit] | [Void | Éditer] | (Weitere Modi nach Sprache), je nach gewählter Sprache

  Wird im Nachhinein eine Sprache mit einem Kürzel angelegt, welches schon als direktes Unterverzeichnis (in der Standardsprache) von / verwendet wird, wird gewarnt, und dringend geraten, diese Seite umzubennen, bzw. das Kürzel zu wechseln
 */

// Global machen
global $versionen, $modi, $startseite, $standardversion, $standardmodus, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS, $DSH_SEITENPFAD;

$DSH_STANDARDSPRACHE = "DE";

$DSH_SPRACHEN = array(
  "DE"  => "Deutsch",
  "EN"  => "English",
  "FR"  => "Français"
);

$versionen = array(
  "DE"  => ["Alt",    "Aktuell",  "Neu"],
  "EN"  => ["Old",    "Current",  "New"],
  "FR"  => ["Ancien", "Actuel",   "Neuveau"]  // Franz-Kenntnisse reichen 'gerade noch so' aus :D
);

$modi = array(
  "DE"  => ["Sehen",  "Bearbeiten"],
  "EN"  => ["View",   "Edit"],
  "FR"  => ["Voir",   "Éditer"]
);

$fehlerSeiten = array(
  404 => array(
    "DE"  => "Fehler/404",
    "EN"  => "Error/404",
    "FR"  => "Erreur/404"
  )
);

$startseite = array(
  "DE"  => "Startseite",
  "EN"  => "Homepage",
  "FR"  => "Accueil"
);


$WEBSITE_URL = [];

$fehler = false;

$standardmodus = 0;
$standardversion = 1;

// Website/Sprache/Version/Modus/Seiten..

$url = $DSH_URL;

if(count($url) > 1) {
  if(in_array($url[1], array_keys($DSH_SPRACHEN))) {
    // Sprache gegeben
    $WEBSITE_URL[0] = $url[1];
    if(count($url) > 2) {
      if(in_array($url[2], $versionen[$WEBSITE_URL[0]])) {
        // Version gegeben
        $WEBSITE_URL[1] = $url[2];
        if(count($url) > 3) {
          if(in_array($url[3], $modi[$WEBSITE_URL[0]])) {
            // Modus gegeben
            $WEBSITE_URL[2] = $url[3];
            if(count($url) > 4) {
              // Seite gegeben

              // Sprache, Version, Modus, Seite
              array_shift($url);
              array_shift($url);
              array_shift($url);
              array_shift($url);
              $WEBSITE_URL = array_merge($WEBSITE_URL, $url);
            } else {
              // Sprache, Version, Modus, keine Seite
              $WEBSITE_URL = array_merge($WEBSITE_URL, [$startseite[$WEBSITE_URL[0]]]);
            }
          } else {
            // Sprache, Version, Seite
            array_shift($url);
            array_shift($url);
            array_shift($url);
            $WEBSITE_URL = array_merge($WEBSITE_URL, [$modi[$WEBSITE_URL[0]][$standardmodus]], $url);
          }
        } else {
          // Sprache, Version, keine Seite
          $WEBSITE_URL = array_merge($WEBSITE_URL, [$modi[$WEBSITE_URL[0]][$standardmodus]], [$startseite[$WEBSITE_URL[0]]]);
        }
      } else {
        // Sprache, Seite
        array_shift($url);
        array_shift($url);
        $WEBSITE_URL = array_merge($WEBSITE_URL, [$versionen[$WEBSITE_URL[0]][$standardversion], $modi[$WEBSITE_URL[0]][$standardmodus]], $url);
      }
    } else {
      // Nur Sprache, keine Seite
      $WEBSITE_URL = array_merge($WEBSITE_URL, [$versionen[$WEBSITE_URL[0]][$standardversion], $modi[$WEBSITE_URL[0]][$standardmodus]], [$startseite[$WEBSITE_URL[0]]]);
    }
  } else {
    // Seite
    array_shift($url);
    $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_STANDARDSPRACHE, $versionen[$DSH_STANDARDSPRACHE][$standardversion], $modi[$DSH_STANDARDSPRACHE][$standardmodus]], $url);
  }
} else {
  // keine Seite
  $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_STANDARDSPRACHE, $versionen[$DSH_STANDARDSPRACHE][$standardversion], $modi[$DSH_STANDARDSPRACHE][$standardmodus]], [$startseite[$DSH_STANDARDSPRACHE]]);
}

// Ab hier ist $WEBSITE_URL eine gültige Seite, OHNE Website/ vorne dran

$DSH_SPRACHE        = $WEBSITE_URL[0];                                          // Sprachkürzel
$DSH_SEITENVERSION  = array_search($WEBSITE_URL[1], $versionen[$DSH_SPRACHE]);  // 0 => Alt     1 => Aktuell  2 => Neu
$DSH_SEITENMODUS    = array_search($WEBSITE_URL[2], $modi[$DSH_SPRACHE]);       // 0 => Sehen   1 => Bearbeiten
$url = $WEBSITE_URL;
$DSH_SEITENPFAD     = array_splice($url, 3);

// Website/Sprache/Version/Modus/Seiten..

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
}

$SEITE = new Seite("Website");

$SEITE[] = UI\Zeile::standard(new UI\Meldung("Gültige URL", "Die URL wurde folgendermaßen interpretiert: <b>".join("/", $WEBSITE_URL)."</b>", "Erfolg"));

?>