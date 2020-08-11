<?php
switch ($meldeid) {
  case 0:
    $gefunden = true;
    Anfrage::setTyp("Meldung");
    Anfrage::setRueck("Meldung", new UI\Meldung("Titek", "Inhatl", "Warnung"));
    $knoepfe[] = new UI\Knopf("Warnung", "Warnung", "Warnung.Nochmal.Warnung");
    $knoepfe[] = UI\Knopf::abbrechen();
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
}
?>
