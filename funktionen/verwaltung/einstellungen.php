<?php
$SEITE = new Kern\Seite("Website", "module.einstellungen");

$spalte = new UI\Spalte("A1", new UI\SeitenUeberschrift("Website"));

$einstellungen = Kern\Einstellungen::ladenAlle("Website");

$reiter = new UI\Reiter("dshModulWebsiteEinstellungen");

// SPRACHEN
$formular    = new UI\FormularTabelle();
$standardsprache = (new UI\Auswahl("dshModulWebsiteStandardsprache"))     ->setWert($einstellungen["Standardsprache"]);
$anf = $DBS->anfrage("SELECT alpha2, name, namestandard FROM website_sprachen");
while($anf->werte($a2, $name, $namestandard)) {
  $standardsprache->add("$name ($namestandard)", $a2);
}
$formular[]  = new UI\FormularFeld(new UI\InhaltElement("Standardsprache:"), $standardsprache);
$formular[]  = (new UI\Knopf("Änderungen speichern", "Erfolg"))           ->setSubmit(true);
$formular    ->addSubmit("website.modul.einstellungen.sprachen()");

$reiterkopf     = new UI\Reiterkopf("Sprachen", new UI\Icon("fas fa-language"));
$reiterspalte   = new UI\Spalte("A1", $formular);
$reiterkoerper  = new UI\Reiterkoerper($reiterspalte->addKlasse("dshUiOhnePadding"));
$reiter[]       = new UI\Reitersegment($reiterkopf, $reiterkoerper);

$spalte[] = $reiter;

$SEITE[] = new UI\Zeile($spalte);
?>
