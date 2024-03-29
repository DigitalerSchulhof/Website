<?php
/**
 * Gibt ein Fenster mit den Details einer Sprache zurück
 * @param  int|null $id Die ID der zu ladenden Sprache
 * @return UI\Fenster
 */
function spracheDetails($id = null) : UI\Fenster {
  if($id === null) {
    $idpre    = "dshVerwaltungSpracheNeu";
    $fenstertitel = "Neue Sprache anlegen";
    $spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Neue Sprache anlegen"));
  } else {
    $idpre    = "dshVerwaltungSpracheBearbeiten$id";
    $fenstertitel = "Sprache bearbeiten";
    $spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Sprache bearbeiten"));
  }

  $formular     = new UI\FormularTabelle();

  $a2           = "";
  $name         = "";
  $namestandard = "";
  $alt          = "";
  $aktuell      = "";
  $neu          = "";
  $sehen        = "";
  $bearbeiten   = "";
  $fehler       = "";

  if($id !== null) {
    global $DBS;
    $DBS->anfrage("SELECT {a2}, {name}, {namestandard}, {alt}, {aktuell}, {neu}, {sehen}, {bearbeiten}, {fehler} FROM website_sprachen WHERE id = ?", "i", $id)
          ->werte($a2, $name, $namestandard, $alt, $aktuell, $neu, $sehen, $bearbeiten, $fehler);
  }


  $formular[] =  new UI\Formularfeld(new UI\Ueberschrift("3", "Namen:"));
  $formular[] =  new UI\FormularFeld(new UI\InhaltElement("Kennung (Alpha-2):"),       (new UI\Textfeld("{$idpre}A2"))           ->setWert($a2));
  $formular[] =  new UI\FormularFeld(new UI\InhaltElement("Name:"),                    (new UI\Textfeld("{$idpre}Name"))         ->setWert($name));
  $formular[] = (new UI\FormularFeld(new UI\InhaltElement("Name (Standardsprache):"),  (new UI\Textfeld("{$idpre}NameStandard")) ->setWert($namestandard)))->setOptional(true);

  $formular[] =  new UI\Formularfeld(new UI\Ueberschrift("3", "Übersetzungen:"));
  $formular[] =  new UI\FormularFeld(new UI\InhaltElement("Alt:"),                     (new UI\Textfeld("{$idpre}Alt"))          ->setWert($alt));
  $formular[] =  new UI\FormularFeld(new UI\InhaltElement("Aktuell:"),                 (new UI\Textfeld("{$idpre}Aktuell"))      ->setWert($aktuell));
  $formular[] =  new UI\FormularFeld(new UI\InhaltElement("Neu:"),                     (new UI\Textfeld("{$idpre}Neu"))          ->setWert($neu));
  $formular[] =  new UI\FormularFeld(new UI\InhaltElement("Sehen:"),                   (new UI\Textfeld("{$idpre}Sehen"))        ->setWert($sehen));
  $formular[] =  new UI\FormularFeld(new UI\InhaltElement("Bearbeiten:"),              (new UI\Textfeld("{$idpre}Bearbeiten"))   ->setWert($bearbeiten));
  $formular[] =  new UI\FormularFeld(new UI\InhaltElement("Fehler:"),                  (new UI\Textfeld("{$idpre}Fehler"))       ->setWert($fehler));

  if($id === null) {
    $formular[] = (new UI\Knopf("Neue Sprache anlegen", "Erfolg"))          ->setSubmit(true);
    $formular   ->addSubmit("website.verwaltung.sprachen.neu.speichern()");
  } else {
    $formular[] = (new UI\Knopf("Änderungen speichern", "Erfolg"))          ->setSubmit(true);
    $formular   ->addSubmit("website.verwaltung.sprachen.bearbeiten.speichern($id)");
  }

  $spalte[]   = $formular;

  return new UI\Fenster($idpre, $fenstertitel, new UI\Zeile($spalte));
}

?>