<?php
/**
 * Gibt ein Fenster mit den Details und Aktionen eines Elements zurück
 * @param  array   $element Assoziatives Array mit Daten zum Element:
 * [
 *    "tabelle" => Die Tabelle, in welcher sich das Element findet
 *    "klasse"  => Der Klassenname der Elements
 * Wenn neues Element:
 *    "position"  => Position, in welches das Element soll
 *    "seite"     => ID der Seite
 *    "sprache"   => Sprache, in welcher der Inhalt angelegt werden soll
 * ]
 * [Tabelle, in welcher sich das Element findet => Klassenname des Elements]
 * @param  string   $klasse Die Klasse des Elements
 * @param  int|null $id Die ID des zu ladenden Elements
 * @return UI\Fenster
 */
function elementDetails($element, $id = null) : UI\Fenster {
  $el         = $element["tabelle"];
  $klasse     = $element["klasse"];
  if($id === null) {
    $position = $element["position"];
    $seite    = $element["seite"];
    $sprache  = $element["sprache"];
  }
  global $DBS;
  if($id === null) {
    $idpre    = "dshWebsiteElementNeu";
    $fenstertitel = "Neues Element anlegen";
    $spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Neues Element anlegen"));
  } else {
    $idpre    = "dshWebsiteElementBearbeiten{$el}_$id";
    $fenstertitel = "Element bearbeiten";
    $spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Element bearbeiten"));
    global $DSH_BENUTZER;
    if($DSH_BENUTZER->hatRecht("website.inhalte.versionen.[|alt,neu].sehen")) {
      $spalte[] = new UI\Meldung("Aktuelle Daten", "Es wird immer, unabhängig davon, welche Version ausgewählt worden ist, der aktuelle Inhalt bearbeitet.", "Information");
    }
  }
  $formular     = new UI\FormularTabelle();

  $status         = "a";

  if($id !== null) {
    $DBS->anfrage("SELECT statusaktuell FROM website__$el WHERE id = ?", "i", $id)
          ->werte($status);
  }

  if($status == "l") {
    $statuswahl = new UI\Auswahl("{$idpre}Status", $status);
    $statuswahl->add("Gelöscht", "l");
    $statuswahl->setDisabled(true);

    $formular[] = new UI\FormularFeld(new UI\InhaltElement("Status:"),                  $statuswahl);

    $formular[] = (new UI\Knopf("Alte Daten wiederherstellen", "Warnung"))->setSubmit(true);
    $formular->addSubmit("@TODO: Alte Daten des Elements wiederherstellen");
    $spalte[]   = $formular;

  } else {
    $statuswahl = new UI\Auswahl("{$idpre}Status", $status);
    $statuswahl ->add("Aktiv",    "a");
    $statuswahl ->add("Inaktiv",  "i");

    $formular[] = new UI\FormularFeld(new UI\InhaltElement("Status:"),                  $statuswahl);
    /** @var Website\Element $elm */
    $elm = new $klasse($id, "aktuell", null);
    foreach($elm->bearbeiten($idpre) as $k => $b) {
      if(intval($k) === $k) {
        $formular[] = new UI\FormularFeld($b);
      } else {
        $formular[] = new UI\FormularFeld(new UI\InhaltElement($k), $b);
      }
    }
    $felder = [];
    foreach($elm->getFelder() as $feld => $attr) {
      $felder[] = $feld;
      $felder[] = $attr;
    }
    $formular[] = new UI\FormularFeld(new UI\VerstecktesFeld("{$idpre}Felder", join(";", $felder)));
    if($id === null) {
      $formular[] = (new UI\Knopf("Neues Element anlegen", "Erfolg")) ->setSubmit(true);
      $formular   ->addSubmit("website.elemente.neu.speichern('$el', $position, $seite, '$sprache')");
    } else {
      $formular[] = (new UI\Knopf("Änderungen speichern", "Erfolg"))  ->setSubmit(true);
      $formular   ->addSubmit("website.elemente.bearbeiten.speichern('$el', $id)");
      global $DSH_BENUTZER;
      if($DSH_BENUTZER->hatRecht("website.inhalte.elemente.löschen")) {
        $formular[] = (new UI\IconKnopf(new UI\Icon(UI\Konstanten::LOESCHEN), "Element löschen", "Fehler"))     ->addFunktion("onclick", "website.elemente.loeschen.fragen('$el', $id)")
                                                                                                                ->setStyle("float", "right");
      }
    }
    $spalte[]   = $formular;

    foreach($elm->bearbeitenPost($idpre) as $f) {
      $spalte[] = $f;
    }
  }

  return new UI\Fenster($idpre, $fenstertitel, new UI\Zeile($spalte));
}

?>