<?php
$spalte = new UI\Spalte("A1", new UI\SeitenUeberschrift("Proto"));

$einstellungen = Kern\Einstellungen::ladenAlle("Proto");

$reiter = new UI\Reiter("dshModulProtoEinstellungen");

$meldung     = new UI\Meldung("Bitte Bankdaten eingeben thx.", "<p>Ab hier wird's lustig!</p>", "Warnung", new UI\Icon("fas fa-exclamation-triangle"));
$formular    = new UI\FormularTabelle();
$daten = (new UI\Textfeld("dshModulProtoBankdaten"))                    ->setWert($einstellungen["Bankdaten"]);
$formular[]  = new UI\FormularFeld(new UI\InhaltElement("Bankdaten:"),  $daten);
$formular[]  = (new UI\Knopf("Änderungen speichern", "Erfolg"))         ->setSubmit(true);
$formular    ->addSubmit("kern.modul.einstellungen.schuldaten()");
$reiterkopf     = new UI\Reiterkopf("Bankdaten");
$reiterspalte   = new UI\Spalte("A1", $meldung, $formular);
$reiterkoerper  = new UI\Reiterkoerper($reiterspalte->addKlasse("dshUiOhnePadding"));
$reiter->addReitersegment(new UI\Reitersegment($reiterkopf, $reiterkoerper));

$spalte[] = $reiter;

$SEITE[] = new UI\Zeile($spalte);
?>
