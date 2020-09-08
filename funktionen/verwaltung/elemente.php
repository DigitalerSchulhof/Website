<?php

use Kern\Verwaltung\Liste;
use Kern\Verwaltung\Element;
use UI\Icon;


$website = Liste::addKategorie(new \Kern\Verwaltung\Kategorie("website", "Website"));

if($DSH_BENUTZER->hatRecht("website.sprachen.sehen"))               $website[] = new Element("Sprachen",        "Sprachen anlegen, bearbeiten und löschen. Standardsprache festlegen.", new Icon(Website\Icons::SPRACHE),         "Schulhof/Verwaltung/Sprachen");
if($DSH_BENUTZER->hatRecht("website.seiten.sehen"))                 $website[] = new Element("Seiten",          "Seiten anlegen, bearbeiten und löschen.", new Icon(Website\Icons::SEITE),           "Schulhof/Verwaltung/Seiten");
if($DSH_BENUTZER->hatRecht("website.sprachen.fehlermeldungen"))     $website[] = new Element("Fehlermeldungen", "Inhalt der Fehlermeldungen pro Sprache bearbeiten.", new Icon(Website\Icons::FEHLERMELDUNG),   "Schulhof/Verwaltung/Sprachen/Fehlermeldungen");

?>
