<?php
/**
 * Gibt ein Fenster mit den Details und Aktionen eines Elements zurück
 * @param  array   $element Assoziatives Array mit Daten zum Element:
 * [
 *    "tabelle" => Die Tabelle, in welcher sich das Element findet
 *    "klasse"  => Der Klassenname der Elements
 *    "sprache" => Die Sprache, in welcher der Inhalt geladen werden soll
 * ]
 * [Tabelle, in welcher sich das Element findet => Klassenname des Elements]
 * @param  string   $klasse Die Klasse des Elements
 * @param  int|null $id Die ID des zu ladenden Elements
 * @param  int|null $position Die Position des neuen Elements
 * In Verwendung, wenn ein neues Element angelegt wird
 * @return UI\Fenster
 */
function elementDetails($element, $id = null, $position = null) : UI\Fenster {
  $el       = $element["tabelle"];
  $klasse   = $element["klasse"];
  $sprache  = $element["sprache"];
  global $DBS;
  if($id === null) {
    $idpre    = "dshWebsiteElementNeu$el";
    $fenstertitel = "Neue Element anlegen";
    $spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Neue Element anlegen"));
  } else {
    $idpre    = "dshWebsiteElementBearbeiten{$el}_$id";
    $fenstertitel = "Element bearbeiten";
    $spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Element bearbeiten"));
  }
  $formular     = new UI\FormularTabelle();

  $status         = "a";

  if($id !== null) {
    $DBS->anfrage("SELECT status FROM website_$el WHERE id = ?", "i", $id)
          ->werte($status);
  }

  $statuswahl = new UI\Auswahl("{$idpre}Status", $status);
  $statuswahl ->add("Aktiv",    "a");
  $statuswahl ->add("Inaktiv",  "i");

  $formular[] = new UI\FormularFeld(new UI\InhaltElement("Status:"),                  $statuswahl);

  $elm = new $klasse($id, $sprache, "aktuell", null);

  $formular[] = new UI\FormularFeld($elm->bearbeiten($idpre));
  $felder = [];
  foreach($elm->getFelder() as $feld => $attr) {
    $felder[] = $feld;
    $felder[] = $attr;
  }
  $formular[] = new UI\FormularFeld(new UI\VerstecktesFeld("{$idpre}Felder", join(";", $felder)));
  if($id === null) {
    $formular[] = (new UI\Knopf("Neues Element anlegen", "Erfolg")) ->setSubmit(true);
    $formular   ->addSubmit("website.elemente.neu.speichern('$el', '$position', '$sprache')");
  } else {
    $formular[] = (new UI\Knopf("Änderungen speichern", "Erfolg"))  ->setSubmit(true);
    $formular   ->addSubmit("website.elemente.bearbeiten.speichern('$el', $id, '$sprache')");
    global $DSH_BENUTZER;
    if($DSH_BENUTZER->hatRecht("website.inhalte.elemente.löschen")) {
      $formular[] = (new UI\IconKnopf(new UI\Icon(UI\Konstanten::LOESCHEN), "Element löschen", "Fehler"))     ->addFunktion("onclick", "website.elemente.loeschen.fragen('$el', $id)")
                                                                                                              ->setStyle("float", "right");
    }
  }

  $spalte[]   = $formular;

  return new UI\Fenster($idpre, $fenstertitel, new UI\Zeile($spalte));
}

?>