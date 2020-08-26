<?php

use Kern\Verwaltung\Liste;
use Kern\Verwaltung\Element;
use UI\Icon;


$website = Liste::addKategorie(new \Kern\Verwaltung\Kategorie("website", "Website"));

if($DSH_BENUTZER->hatRecht("website.sprachen.sehen"))                 $website[] = new Element("Sprachen", null, new Icon("fas fa-language"), "Schulhof/Verwaltung/Sprachen");

?>
