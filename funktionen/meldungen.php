<?php
switch ($meldeid) {
  case 0:
    Anfrage::setRueck("Meldung", new UI\Meldung("Sprache anlegen", "Die Sprache wurde angelegt.", "Erfolg"));
    $knoepfe[] = UI\Knopf::ok("dshVerwaltungSpracheNeu");
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 1:
    parameter("id");
    Anfrage::setRueck("Meldung", new UI\Meldung("Sprache bearbeiten", "Die Sprache wurde bearbeitet.", "Erfolg"));
    $knoepfe[] = UI\Knopf::ok("dshVerwaltungSpracheBearbeiten$id");
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
  case 6:
    Anfrage::setRueck("Meldung", new UI\Meldung("Seite anlegen", "Die Seite wurde angelegt.", "Erfolg"));
    $knoepfe[] = UI\Knopf::ok("dshVerwaltungSeiteNeu");
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 7:
    parameter("id");
    Anfrage::setRueck("Meldung", new UI\Meldung("Seite bearbeiten", "Die Seite wurde bearbeitet.", "Erfolg"));
    $knoepfe[] = UI\Knopf::ok("dshVerwaltungSeiteBearbeiten$id");
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 8:
    parameter("id");
    Anfrage::setRueck("Meldung", new UI\Meldung("Seite löschen", "Soll die Seite wirklich gelöscht werden? Dadurch gehen <b>alle</b> Inhalte, welche mit dieser Seite verbunden sind, sowie <b>alle</b> Unterseiten dieser Seite unwiederruflich verloren!", "Warnung"));
    $knoepfe[] = new UI\Knopf("Seite löschen", "Fehler", "website.verwaltung.seiten.loeschen.ausfuehren($id)");
    $knoepfe[] = UI\Knopf::abbrechen();
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 9:
    Anfrage::setRueck("Meldung", new UI\Meldung("Seite löschen", "Die Seite wurde gelöscht.", "Erfolg"));
    break;
  case 10:
    parameter("id");
    Anfrage::setRueck("Meldung", new UI\Meldung("Zur Startseite machen", "Soll die Seite wirklich zur Startseite gemacht werden?", "Warnung"));
    $knoepfe[] = new UI\Knopf("Zur Startseite machen", "Erfolg", "website.verwaltung.seiten.startseite.ausfuehren($id)");
    $knoepfe[] = UI\Knopf::abbrechen();
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 11:
    Anfrage::setRueck("Meldung", new UI\Meldung("Zur Startseite machen", "Die Sprache wurde zur Startseite gemacht.", "Erfolg"));
    break;
  case 12:
    Anfrage::setRueck("Meldung", new UI\Meldung("Element anlegen", "Das Element wurde angelegt.", "Erfolg"));
    $knoepfe[] = (UI\Knopf::ok("dshWebsiteElementNeu"))->addFunktion("onclick", "core.neuladen()");
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 13:
    parameter("element", "id");
    Anfrage::setRueck("Meldung", new UI\Meldung("Element bearbeiten", "Das Element wurde bearbeitet.", "Erfolg"));
    $knoepfe[] = (UI\Knopf::ok("dshWebsiteElementBearbeiten{$element}_$id"))->addFunktion("onclick", "core.neuladen()");
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 14:
    parameter("version", "id", "sprache");
    if($version == "a") {
      Anfrage::setRueck("Meldung", new UI\Meldung("Daten wiederherstellen", "Soll die Daten wirklich wiederhergestellt werden? Die aktuellen Daten werden dadurch überschrieben.", "Warnung"));
      $knoepfe[] = new UI\Knopf("Daten wiederherstellen", "Erfolg", "website.verwaltung.seiten.setzen.version.ausfuehren($id, 'a', '$sprache')");
    } else {
      Anfrage::setRueck("Meldung", new UI\Meldung("Daten freigeben", "Soll die Daten wirklich freigegeben werden?", "Warnung"));
      $knoepfe[] = new UI\Knopf("Daten freigeben", "Erfolg", "website.verwaltung.seiten.setzen.version.ausfuehren($id, 'n', '$sprache')");
    }
    $knoepfe[] = UI\Knopf::abbrechen();
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 15:
    parameter("version");
    if($version == "a") {
      Anfrage::setRueck("Meldung", new UI\Meldung("Daten wiederherstellen", "Die alten Daten wurden wiederhergestellt.", "Erfolg"));
    } else {
      Anfrage::setRueck("Meldung", new UI\Meldung("Daten freigeben", "Die neuen Daten wurden freigegeben.", "Erfolg"));
    }
    $knoepfe[] = (UI\Knopf::ok())->addFunktion("onclick", "core.neuladen()");
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 16:
    parameter("element", "id");
    Anfrage::setRueck("Meldung", new UI\Meldung("Element löschen", "Soll das Element wirklich gelöscht werden?", "Warnung"));
    $knoepfe[] = new UI\Knopf("Element löschen", "Fehler", "website.elemente.loeschen.ausfuehren('$element', $id)");
    $knoepfe[] = UI\Knopf::abbrechen();
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
  case 17:
    Anfrage::setRueck("Meldung", new UI\Meldung("Element löschen", "Das Element wurde gelöscht.", "Erfolg"));
    $knoepfe[] = (UI\Knopf::ok())->addFunktion("onclick", "core.neuladen()");
    Anfrage::setRueck("Knöpfe", $knoepfe);
    break;
}
?>
