<?php
namespace Website;
use UI;

/*

  Ideale URL (RegEx): sei
  {z} : [\-\:\(\)\[\]\{\}äöüßáéíóúàèìòùæâêîôûøØÇãåçëïõÿñ0-9a-z]     Eine Seitenbezeichnung
  {s} : ((?:{z}+\/)*(?:{z}+)+)                                      Der komplette Seitenpfad

  /^Website\/(DE|EN|FR)\/(Alt|Aktuell|Neu)\/(Sehen|Bearbeiten)\/{s}$/i
    => /^Website\/(DE|EN|FR)\/(Alt|Aktuell|Neu)\/(Sehen|Bearbeiten)\/((?:[\-\:\(\)\[\]\{\}äöüßáéíóúàèìòùæâêîôûøØÇãåçëïõÿñ0-9a-z]+\/)*(?:[\-\:\(\)\[\]\{\}äöüßáéíóúàèìòùæâêîôûøØÇãåçëïõÿñ0-9a-z]+)+)$/i

  Bei Sprache, Version und Modus sind die jeweils Vorherigen notwendig, um eine gültige URL zu bilden.

  Abkürzen ist insoweit möglich, als bei Sprache »DE«, bei Version »Aktuell«, und bei Modus »Sehen« angenommen wird, sofern dises nicht anders gegeben ist:


  Website/{s}                     -> Website/DE/Aktuell/Sehen/{s}
  Website/Sehen/{s}               -> Ungültig, da Sprache fehlt
  Website/Bearbeiten/{s}          -> Ungültig, da Sprache und Version fehlen
  Website/EN/{s}                  -> Website/EN/Current/View/{s}
  Website/DE/Aktuell/{s}          -> Website/DE/Aktuell/Sehen/{s}
  Website/FR/Ancien/{s}           -> Website/FR/Ancien/Voir/{s}
  Website/DE/Neu/Bearbeiten/{s}   -> Website/DE/Neu/Bearbeiten/{s}

  Ist Sprache, Version oder Modus gegeben, muss {s} angegeben sein.

  Beim Anlegen einer direkten Unterseite von /Website sind folgende Bezeichnungen nicht erlaubt:
  - DE | EN | FR | (Weitere Sprachkürzel)
  - [Alt | Aktuell | Neu] | [Old | Current | New] | [Ancien | Actuel | Neuveau] | (Weitere Versionen nach Sprache), je nach gewählter Sprache
  - [Sehen | Bearbeiten] | [View | Edit] | [Void | Éditer] | (Weitere Modi nach Sprache), je nach gewählter Sprache

  Wird im Nachhinein eine Sprache mit einem Kürzel angelegt, welches schon als direktes Unterverzeichnis (in der Standardsprache) von / verwendet wird, wird gewarnt, und dringend geraten, diese Seite umzubennen, bzw. das Kürzel zu wechseln
 */

// Global machen
global $versionen, $modi, $startseite, $standardversion, $standardmodus, $DSH_STANDARDSPRACHE, $DSH_SPRACHE, $DSH_SEITENVERSION, $DSH_SEITENMODUS, $DSH_SEITENPFAD;

$DSH_STANDARDSPRACHE = \Kern\Einstellungen::laden("Website", "Standardsprache");

$DSH_SPRACHEN = [];
$versionen    = [];
$modi         = [];
$startseite   = [];

// Startseite nimmt, wenn vorhanden den Pfad der Sprache, ansonsten Fallback der Standardsprache
$anf = $DBS->anfrage("SELECT {a2}, {name}, {namestandard}, {alt}, {aktuell}, {neu}, {sehen}, {bearbeiten}, {(SELECT IF(wsd.pfad IS NULL, (SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.pfad) FROM website_seitendaten as wsd WHERE wsd.sprache = wsp.id AND wsd.seite = (SELECT id FROM website_seiten WHERE startseite = 1))} FROM website_sprachen as wsp");
while($anf->werte($a2, $name, $namestd, $alt, $aktuell, $neu, $sehen, $bearbeiten, $s)) {
  $name       = str_replace(" ", "_", $name);
  $namestd    = str_replace(" ", "_", $namestd);
  $alt        = str_replace(" ", "_", $alt);
  $aktuell    = str_replace(" ", "_", $aktuell);
  $neu        = str_replace(" ", "_", $neu);
  $sehen      = str_replace(" ", "_", $sehen);
  $bearbeiten = str_replace(" ", "_", $bearbeiten);
  $s          = str_replace(" ", "_", $s);

  $DSH_SPRACHEN [$a2] = [$name, $namestd];
  $versionen    [$a2] = [$alt, $aktuell, $neu];
  $modi         [$a2] = [$sehen, $bearbeiten];
  $startseite   [$a2] =  $s;
}
$WEBSITE_URL = [];

$standardmodus = 0;
$standardversion = 1;

// Website/Sprache/Version/Modus/Seiten..

$url = $DSH_URL;
if($url[0] === "Website" || $url[0] === "") {
  array_shift($url);
}

if(count($url) > 0) {
  if(in_array($url[0], array_keys($DSH_SPRACHEN))) {
    // Sprache gegeben
    $WEBSITE_URL[0] = $url[0];
    if(count($url) > 1) {
      if(in_array($url[1], $versionen[$WEBSITE_URL[0]])) {
        // Version gegeben
        $WEBSITE_URL[1] = $url[1];
        if(count($url) > 2) {
          if(in_array($url[2], $modi[$WEBSITE_URL[0]])) {
            // Modus gegeben
            $WEBSITE_URL[2] = $url[2];
            if(count($url) > 4) {
              // Seite gegeben

              // Sprache, Version, Modus, Seite
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
            $WEBSITE_URL = array_merge($WEBSITE_URL, [$modi[$WEBSITE_URL[0]][$standardmodus]], $url);
          }
        } else {
          // Sprache, Version, keine Seite
          $WEBSITE_URL = array_merge($WEBSITE_URL, [$modi[$WEBSITE_URL[0]][$standardmodus]], [$startseite[$WEBSITE_URL[0]]]);
        }
      } else {
        // Sprache, Seite
        array_shift($url);
        $WEBSITE_URL = array_merge($WEBSITE_URL, [$versionen[$WEBSITE_URL[0]][$standardversion], $modi[$WEBSITE_URL[0]][$standardmodus]], $url);
      }
    } else {
      // Nur Sprache, keine Seite
      $WEBSITE_URL = array_merge($WEBSITE_URL, [$versionen[$WEBSITE_URL[0]][$standardversion], $modi[$WEBSITE_URL[0]][$standardmodus]], [$startseite[$WEBSITE_URL[0]]]);
    }
  } else {
    // Seite
    $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_STANDARDSPRACHE, $versionen[$DSH_STANDARDSPRACHE][$standardversion], $modi[$DSH_STANDARDSPRACHE][$standardmodus]], $url);
  }
} else {
  // keine Seite
  $WEBSITE_URL = array_merge($WEBSITE_URL, [$DSH_STANDARDSPRACHE, $versionen[$DSH_STANDARDSPRACHE][$standardversion], $modi[$DSH_STANDARDSPRACHE][$standardmodus], $startseite[$DSH_STANDARDSPRACHE]]);
}

// Ab hier ist $WEBSITE_URL eine gültige Seite, OHNE Website/ vorne dran

$DSH_SPRACHE        = $WEBSITE_URL[0];                                          // Sprachkürzel
$DSH_SEITENVERSION  = array_search($WEBSITE_URL[1], $versionen[$DSH_SPRACHE]);  // 0 => Alt     1 => Aktuell  2 => Neu
$DSH_SEITENMODUS    = array_search($WEBSITE_URL[2], $modi[$DSH_SPRACHE]);       // 0 => Sehen   1 => Bearbeiten
$url = $WEBSITE_URL;
$DSH_SEITENPFAD     = array_splice($url, 3);

// Website/Sprache/Version/Modus/Seiten..
$SEITE   = Seite::vonPfad($DSH_SPRACHE, $DSH_SEITENPFAD, $DSH_SEITENVERSION, $DSH_SEITENMODUS);

$SEITE[] = UI\Zeile::standard(new Sprachwahl("dshWebsiteSprache", $DSH_SPRACHE, "website.seite.aendern.sprache()"));
?>