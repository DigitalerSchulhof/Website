<?php
$SEITE = new Kern\Seite("Seite1", null);

$spalte    = new UI\Spalte("A1");
$spalte[]  = new UI\SeitenUeberschrift("Seite 1");
$profil    = (new Kern\Profil($DSH_BENUTZER))->getProfil();
$spalte[]  = $profil;

$SEITE[]   = new UI\Zeile($spalte);
?>
