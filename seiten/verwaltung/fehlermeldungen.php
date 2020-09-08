<?php
if(count($DSH_URL) > 4) {
  $a2 = $DSH_URL[4];
  if(!$DBS->anfrage("SELECT id, {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')) FROM website_sprachen WHERE a2 = [?]", "s", $a2)
            ->werte($id, $a2, $name)) {
    Seite::nichtGefunden();
  }
  $DBS->anfrage("SELECT {a2} FROM website_sprachen WHERE a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)")
        ->werte($standard);
} else {
  $DBS->anfrage("SELECT id, {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')) FROM website_sprachen WHERE a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)")
        ->werte($id, $a2, $name);
  $standard = $a2;
}
$SEITE = new Kern\Seite("Sprachen", "website.sprachen.fehlermeldungen");

$spalte = new UI\Spalte("A1", new UI\SeitenUeberschrift("Fehlermeldungen auf $name"));

$titel = $inhalte = [];

$anf = $DBS->anfrage("SELECT fehler, IF(titel IS NULL, '', {titel}), IF(inhalt IS NULL, '', {inhalt}) FROM website_fehlermeldungen as wfm WHERE sprache = ?", "i", $id);
while($anf->werte($fehler, $tit, $inhalt)) {
  $titel[$fehler]   = $tit;
  $inhalte[$fehler] = $inhalt;
}

$anf = $DBS->anfrage("SELECT fehler, IF(titel IS NULL, '', {titel}), IF(inhalt IS NULL, '', {inhalt}) FROM website_fehlermeldungen as wfm WHERE sprache = (SELECT id FROM website_sprachen WHERE a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))");
while($anf->werte($fehler, $tit, $inhalt)) {
  $titel["s$fehler"]   = $tit;
  $inhalte["s$fehler"] = $inhalt;
}

$form = new UI\FormularTabelle();

$tit    = new UI\Textfeld("dshVerwaltungFehlermeldungTitel403");
$inh    = new UI\Editor("dshVerwaltungFehlermeldungInhalt403");
$tit    ->setWert($titel["403"]);
$tit    ->setPlatzhalter($titel["s403"]);
$inh    ->setWert($inhalte["403"]);
$inh    ->setPlatzhalter($inhalte["s403"]);
$form[] = new UI\FormularFeld(new UI\Ueberschrift("3", "Fehler 403:"));
$form[] = new UI\FormularFeld(new UI\Meldung($tit, $inh, "Fehler"));

$tit    = new UI\Textfeld("dshVerwaltungFehlermeldungTitel404");
$inh    = new UI\Editor("dshVerwaltungFehlermeldungInhalt404");
$tit    ->setWert($titel["404"]);
$tit    ->setPlatzhalter($titel["s404"]);
$inh    ->setWert($inhalte["404"]);
$inh    ->setPlatzhalter($inhalte["s404"]);
$form[] = new UI\FormularFeld(new UI\Ueberschrift("3", "Fehler 404:"));
$form[] = new UI\FormularFeld(new UI\Meldung($tit, $inh, "Fehler"));

$form[]   = (new UI\Knopf("Änderungen speichern", "Erfolg"))->setSubmit(true);
$form     ->addSubmit("website.verwaltung.fehlermeldungen.speichern('$a2')");

$spalte[] = $form;

$sprachwahl = new UI\Auswahl("dshVerwaltungFehlermeldungenSprachwahl", $a2);
$sprachwahl ->addFunktion("oninput", "website.verwaltung.fehlermeldungen.sprache()");
$sprachwahl ->setStyle("margin-top", "10px");

$anf = $DBS->anfrage("SELECT {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')) as bezeichnung FROM website_sprachen");
while($anf->werte($aa2, $bez)) {
  if($aa2 == $standard) {
    $pf = "";
  } else {
    $pf = "/$aa2";
  }
  $sprachwahl->add($bez, "Schulhof/Verwaltung/Sprachen/Fehlermeldungen$pf", $a2 == $aa2);
}
$sprachwahl->addKlasse("dshUiEingabefeldKlein");
$sprachwahl->setStyle("float", "right");

$spalte[] = $sprachwahl;

$SEITE[] = new UI\Zeile($spalte);
?>
