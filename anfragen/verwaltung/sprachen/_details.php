<?php
/**
 * Gibt die Details einer Sprache aus
 * @param  int|null $id Die ID der zu ladenden Sprache
 * @return string
 */
function spracheDetails($id = null) : string {
  $formularName         = new UI\FormularTabelle();
  $formularBez          = new UI\FormularTabelle();

  if($id === null) {
    $idpre = "dshVerwaltungSpracheNeu";
  } else {
    $idpre = "dshVerwaltungSpracheBearbeiten$id";
  }

  $a2           = "";
  $name         = "";
  $namestandard = "";
  $alt          = "";
  $aktuell      = "";
  $neu          = "";
  $sehen        = "";
  $bearbeiten   = "";
  $fehler       = "";
  $startseite   = "";

  if($id !== null) {
    global $DBS;
    $DBS->anfrage("SELECT {a2}, {name}, {namestandard}, {alt}, {aktuell}, {neu}, {sehen}, {bearbeiten}, {fehler}, {startseite} FROM website_sprachen WHERE id = ?", "i", $id)
          ->werte($a2, $name, $namestandard, $alt, $aktuell, $neu, $sehen, $bearbeiten, $fehler, $startseite);
  }


  $formularName[]   = new UI\FormularFeld(new UI\InhaltElement("Kennung (Alpha-2):"),       (new UI\Textfeld("{$idpre}A2"))           ->setWert($a2));
  $formularName[]   = new UI\FormularFeld(new UI\InhaltElement("Name:"),                    (new UI\Textfeld("{$idpre}Name"))         ->setWert($name));
  $formularName[]   = new UI\FormularFeld(new UI\InhaltElement("Name (Standardsprache):"), (new UI\Textfeld("{$idpre}NameStandard")) ->setWert($namestandard));
  $formularBez[]    = new UI\FormularFeld(new UI\InhaltElement("Alt:"),                     (new UI\Textfeld("{$idpre}Alt"))          ->setWert($alt));
  $formularBez[]    = new UI\FormularFeld(new UI\InhaltElement("Aktuell:"),                 (new UI\Textfeld("{$idpre}Aktuell"))      ->setWert($aktuell));
  $formularBez[]    = new UI\FormularFeld(new UI\InhaltElement("Neu:"),                     (new UI\Textfeld("{$idpre}Neu"))          ->setWert($neu));
  $formularBez[]    = new UI\FormularFeld(new UI\InhaltElement("Sehen:"),                   (new UI\Textfeld("{$idpre}Sehen"))        ->setWert($sehen));
  $formularBez[]    = new UI\FormularFeld(new UI\InhaltElement("Bearbeiten:"),              (new UI\Textfeld("{$idpre}Bearbeiten"))   ->setWert($bearbeiten));
  $formularBez[]    = new UI\FormularFeld(new UI\InhaltElement("Fehler:"),                  (new UI\Textfeld("{$idpre}Fehler"))       ->setWert($fehler));
  $formularBez[]    = new UI\FormularFeld(new UI\InhaltElement("Startseite:"),              (new UI\Textfeld("{$idpre}Startseite"))   ->setWert($startseite));

  if($id === null) {
    $formularBez[] = (new UI\Knopf("Neue Sprache anlegen", "Erfolg"))          ->setSubmit(true);
    $formularBez   ->addSubmit("website.verwaltung.sprachen.neu.speichern()");
    $formularBez[] = (new UI\Knopf("Abbrechen", "Fehler"))                     ->addFunktion("onclick", "ui.fenster.schliessen('dshVerwaltungSpracheNeu')");
  } else {
    $formularBez[] = (new UI\Knopf("Änderungen speichern", "Erfolg"))          ->setSubmit(true);
    $formularBez   ->addSubmit("website.verwaltung.sprachen.bearbeiten.speichern($id)");
    $formularBez[] = (new UI\Knopf("Abbrechen", "Fehler"))                     ->addFunktion("onclick", "ui.fenster.schliessen('dshVerwaltungSpracheBearbeiten')");
  }
  $r  = "";
  $r .= new UI\Ueberschrift("3", "Namen:");
  $r .= $formularName;
  $r .= new UI\Ueberschrift("3", "Übersetzungen:");
  $r .= $formularBez;
  return $r;
}

?>