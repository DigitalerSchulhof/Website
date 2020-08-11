<?php
$SEITE = new Kern\Seite("Seite2", "proto.seite2.sehen");

$spalte    = new UI\Spalte("A1");
$spalte[]  = new UI\SeitenUeberschrift("Seite 2");
$profil    = (new Kern\Profil($DSH_BENUTZER))->getProfil();
$spalte[]  = $profil;

$SEITE[]   = new UI\Zeile($spalte);
?>
