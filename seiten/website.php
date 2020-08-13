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


$url = $DSH_URL;

$fehler = false;

switch(count($url)) {
  case 1:
    $url = array_merge($url, [$DSH_STANDARDSPRACHE, $versionen[$DSH_STANDARDSPRACHE][1], $modi[$DSH_STANDARDSPRACHE][1], $startseite[$DSH_STANDARDSPRACHE]]);
    break;
  case 2:
    if(!in_array($url[1], array_keys($DSH_SPRACHEN))) {
      $fehler = true;
    } else {
      $url = array_merge($url, [$versionen[$url[1]][1], $modi[$url[1]][0], $startseite[$url[1]]]);
    }
    break;
  case 3:
    if(!in_array($url[1], array_keys($DSH_SPRACHEN)) || !in_array($url[2], $modi[$url[1]])) {
      $fehler = true;
    } else {
      $url = array_merge($url, [$modi[$url[1]][0], $startseite[$url[1]]]);
    }
    break;
  case 3:
    if(!in_array($url[1], array_keys($DSH_SPRACHEN)) || !in_array($url[2], $versionen[$url[1]]) || !in_array($url[3], $modi[$url[1]])) {
      $fehler = true;
    } else {
      $url = array_merge($url, [$startseite[$url[1]]]);
    }
    break;
  default:
    if(!in_array($url[1], array_keys($DSH_SPRACHEN)) || !in_array($url[2], $versionen[$url[1]]) || !in_array($url[3], $modi[$url[1]])) {
      $fehler = true;
    }
    break;
}

// Ab hier ist $url eine gültige Seite

if($fehler) {
  Anfrage::seiteAus($fehlerSeiten[$url[1]]);
}

$SEITE = new Kern\Seite("Website", false);

$SEITE[] = UI\Zeile::standard(new UI\Meldung("Gültige URL", "Die URL wurde folgendermaßen interpretiert: <b>".join("/", $url)."</b>", "Erfolg"));

?>