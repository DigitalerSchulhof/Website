<?php
switch ($meldeid) {
  case 0:
    Anfrage::setRueck("Meldung", new UI\Meldung("Sprache anlegen", "Die Sprache wurde angelegt.", "Erfolg"));
    $knoepfe = [UI\Knopf::ok("dshVerwaltungSpracheNeu")];
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 1:
    Anfrage::setRueck("Meldung", new UI\Meldung("Sprache bearbeiten", "Die Sprache wurde bearbeitet.", "Erfolg"));
    $knoepfe = [UI\Knopf::ok("dshVerwaltungSpracheBearbeiten")];
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 2:
    parameter("id");
    Anfrage::setRueck("Meldung", new UI\Meldung("Sprache löschen", "Soll die Sprache wirklich gelöscht werden? Dadurch gehen <b>alle</b> Inhalte, welche mit dieser Sprache verbunden sind, unwiederruflich verloren!", "Warnung"));
    $knoepfe[] = new UI\Knopf("Sprache löschen", "Fehler", "website.verwaltung.sprachen.loeschen.ausfuehren($id)");
    $knoepfe[] = UI\Knopf::abbrechen();
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 3:
    Anfrage::setRueck("Meldung", new UI\Meldung("Sprache löschen", "Die Sprache wurde gelöscht.", "Erfolg"));
    break;
  case 4:
    parameter("id");
    Anfrage::setRueck("Meldung", new UI\Meldung("Zur Standardsprache machen", "Soll die Sprache wirklich zur Standardsprache gemacht werden?", "Warnung"));
    $knoepfe[] = new UI\Knopf("Zur Standardsprache machen", "Erfolg", "website.verwaltung.sprachen.standardsprache.ausfuehren($id)");
    $knoepfe[] = UI\Knopf::abbrechen();
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 5:
    Anfrage::setRueck("Meldung", new UI\Meldung("Zur Standardsprache machen", "Die Sprache wurde zur Standardsprache gemacht.", "Erfolg"));
    break;
}
?>
