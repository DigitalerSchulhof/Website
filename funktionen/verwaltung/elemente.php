<?php

use Kern\Verwaltung\Liste;
use Kern\Verwaltung\Element;
use UI\Icon;


$website = Liste::addKategorie(new \Kern\Verwaltung\Kategorie("website", "Website"));

if($DSH_BENUTZER->hatRecht("website.sprachen.sehen")) $website[] = new Element("Sprachen", null, new Icon(Website\Icons::SPRACHE), "Schulhof/Verwaltung/Sprachen");
if($DSH_BENUTZER->hatRecht("website.seiten.sehen"))   $website[] = new Element("Seiten",   null, new Icon(Website\Icons::SEITE),   "Schulhof/Verwaltung/Seiten");

?>
