<?php
namespace Website;
use Kern;

/*
  Bei Sprache, Version und Modus sind die jeweils Vorherigen notwendig, um eine gültige URL zu bilden.

  Abkürzen ist insoweit möglich, als bei Sprache »DE«, bei Version »Aktuell«, und bei Modus »Sehen« angenommen wird, sofern dises nicht anders gegeben ist:


  {s}                     -> DE/Aktuell/Sehen/{s}
  Sehen/{s}               -> Ungültig, da Sprache fehlt
  Bearbeiten/{s}          -> Ungültig, da Sprache und Version fehlen
  EN/{s}                  -> EN/Current/View/{s}
  DE/Aktuell/{s}          -> DE/Aktuell/Sehen/{s}
  FR/Ancien/{s}           -> FR/Ancien/Voir/{s}
  DE/Neu/Bearbeiten/{s}   -> DE/Neu/Bearbeiten/{s}

  Ist Sprache, Version oder Modus gegeben, muss {s} angegeben sein.

  Beim Anlegen einer direkten Unterseite von /Website sind folgende Bezeichnungen nicht erlaubt:
  - DE | EN | FR | (Weitere Sprachkürzel)
  - [Alt | Aktuell | Neu] | [Old | Current | New] | [Ancien | Actuel | Neuveau] | (Weitere Versionen nach Sprache), je nach gewählter Sprache
  - [Sehen | Bearbeiten] | [View | Edit] | [Void | Éditer] | (Weitere Modi nach Sprache), je nach gewählter Sprache

  Wird im Nachhinein eine Sprache mit einem Kürzel angelegt, welches schon als direktes Unterverzeichnis (in der Standardsprache) von / verwendet wird, wird gewarnt, und dringend geraten, diese Seite umzubennen, bzw. das Kürzel zu wechseln
 */

// Global machen
global $DSH_SPRACHEN, $DSH_SEITENVERSIONEN, $DSH_SEITENMODI, $DSH_STARTSEITE, $WEBSITE_URL;

$DSH_SPRACHEN         = [];
$DSH_SEITENVERSIONEN  = [];
$DSH_SEITENMODI       = [];
$DSH_STARTSEITE       = [];

// Startseite nimmt, wenn vorhanden den Pfad der Sprache, ansonsten Fallback der Standardsprache
$anf = $DBS->anfrage("SELECT {a2}, {alt}, {aktuell}, {neu}, {sehen}, {bearbeiten}, {(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = wsd.seite AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))) FROM website_seitendaten as wsd WHERE wsd.sprache = wsp.id AND wsd.seite = (SELECT id FROM website_seiten WHERE startseite = 1))} FROM website_sprachen as wsp");
while($anf->werte($a2, $alt, $aktuell, $neu, $sehen, $bearbeiten, $s)) {
  $DSH_SPRACHEN         []    = $a2;
  $DSH_SEITENVERSIONEN  [$a2] = array(
    "alt"             => $alt,
    "aktuell"         => $aktuell,
    "neu"             => $neu);
  $DSH_SEITENMODI       [$a2] = array(
    "sehen"           => $sehen,
    "bearbeiten"      => $bearbeiten);
  $DSH_STARTSEITE       [$a2] = $s;
}

$DSH_SPRACHEN         = Kern\Texttrafo::text2url($DSH_SPRACHEN);
$DSH_SEITENVERSIONEN  = Kern\Texttrafo::text2url($DSH_SEITENVERSIONEN);
$DSH_SEITENMODI       = Kern\Texttrafo::text2url($DSH_SEITENMODI);
$DSH_STARTSEITE       = Kern\Texttrafo::text2url($DSH_STARTSEITE);
$WEBSITE_URL          = [];

// Sprache/Version/Modus/Seiten..

$url = $DSH_URL;
if($url[0] === "") {
  array_shift($url);
}
if(count($url) > 0) {
  if(in_array($url[0], $DSH_SPRACHEN)) {
    // Sprache gegeben
    $WEBSITE_URL[0] = $url[0];
    if(count($url) > 1) {
      if(in_array($url[1], $DSH_SEITENVERSIONEN[$WEBSITE_URL[0]])) {
        // Version gegeben
        $WEBSITE_URL[1] = $url[1];
        if(count($url) > 2) {
          if(in_array($url[2], $DSH_SEITENMODI[$WEBSITE_URL[0]])) {
            // Modus gegeben
            $WEBSITE_URL[2] = $url[2];
            if(count($url) > 3) {
              // Seite gegeben

              // Sprache, Version, Modus, Seite
              array_shift($url);
              array_shift($url);
              array_shift($url);
              $WEBSITE_URL = array_merge($WEBSITE_URL, $url);
            } else {
              // Sprache, Version, Modus, keine Seite
              $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_STARTSEITE[$WEBSITE_URL[0]]]);
            }
          } else {
            // Sprache, Version, Seite
            array_shift($url);
            array_shift($url);
            $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_SEITENMODI[$WEBSITE_URL[0]][STANDARDMODUS]], $url);
          }
        } else {
          // Sprache, Version, keine Seite
          $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_SEITENMODI[$WEBSITE_URL[0]][STANDARDMODUS]], [$DSH_STARTSEITE[$WEBSITE_URL[0]]]);
        }
      } else {
        // Sprache, Seite
        array_shift($url);
        $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_SEITENVERSIONEN[$WEBSITE_URL[0]][STANDARDVERSION], $DSH_SEITENMODI[$WEBSITE_URL[0]][STANDARDMODUS]], $url);
      }
    } else {
      // Nur Sprache, keine Seite
      $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_SEITENVERSIONEN[$WEBSITE_URL[0]][STANDARDVERSION], $DSH_SEITENMODI[$WEBSITE_URL[0]][STANDARDMODUS]], [$DSH_STARTSEITE[$WEBSITE_URL[0]]]);
    }
  } else {
    // Seite
    $WEBSITE_URL = array_merge($WEBSITE_URL, [STANDARDSPRACHE, $DSH_SEITENVERSIONEN[STANDARDSPRACHE][STANDARDVERSION], $DSH_SEITENMODI[STANDARDSPRACHE][STANDARDMODUS]], $url);
  }
} else {
  // keine Seite
  $DSH_URL = [$DSH_STARTSEITE[STANDARDSPRACHE]];
  $WEBSITE_URL = array_merge($WEBSITE_URL, [STANDARDSPRACHE, $DSH_SEITENVERSIONEN[STANDARDSPRACHE][STANDARDVERSION], $DSH_SEITENMODI[STANDARDSPRACHE][STANDARDMODUS], $DSH_STARTSEITE[STANDARDSPRACHE]]);
}

$sprache  = $WEBSITE_URL[0];
$version  = array_search($WEBSITE_URL[1], $DSH_SEITENVERSIONEN[$sprache]);
$modus    = array_search($WEBSITE_URL[2], $DSH_SEITENMODI[$sprache]);

$pfad = array_splice($WEBSITE_URL, 3);
$SEITE = Seite::vonPfad($sprache, $pfad, $version, $modus);
?>