<?php
/**
 * Gibt ein Fenster mit den Details einer Seite zurück
 * @param  int|null $id Die ID der zu ladenden Seite
 * @param  int|null $zugehoerig Die ID der zugehörigen Seite
 * In Verwendung, wenn eine neue Seite angelegt wird.
 * @return UI\Fenster
 */
function seiteDetails($id = null, $zugehoerig = null) : UI\Fenster {
  global $DBS;
  if($id === null) {
    $idpre    = "dshVerwaltungSeiteNeu";
    $fenstertitel = "Neue Seite anlegen";
    $spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Neue Seite anlegen"));
  } else {
    $idpre    = "dshVerwaltungSeiteBearbeiten$id";
    $fenstertitel = "Seite bearbeiten";
    $spalte   = new UI\Spalte("A1", new UI\SeitenUeberschrift("Seite bearbeiten"));
  }

  $spalte[]     = new UI\Meldung("Standardsprache", "Wird eine Bezeichnung oder ein Pfadabschnitt in einer Sprache nicht ausgefüllt, wird auf den Wert der Standardsprache zurückgefallen.", "Information");

  $formular     = new UI\FormularTabelle();

  $art            = "i";
  $status         = "a";

  if($id !== null) {
    $DBS->anfrage("SELECT art, status FROM website_seiten WHERE id = ?", "i", $id)
          ->werte($art, $status);
  }

  $artwahl = new UI\Auswahl("{$idpre}Art", $art);
  $artwahl ->add("Seite mit Inhalt",          "i");
  $artwahl ->add("Menüseite mit Unterseiten", "m");

  $statuswahl = new UI\Auswahl("{$idpre}Status", $status);
  $statuswahl ->add("Aktiv",    "a");
  $statuswahl ->add("Inaktiv",  "i");

  $formular[] = new UI\Formularfeld(new UI\Ueberschrift("3", "Allgemeines:"));
  $formular[] = new UI\FormularFeld(new UI\InhaltElement("Art:"),                     $artwahl);
  $formular[] = new UI\FormularFeld(new UI\InhaltElement("Status:"),                  $statuswahl);


  $DBS->anfrage("SELECT ws.id, {ws.a2}, {ws.name} FROM website_sprachen as ws JOIN website_einstellungen as we ON we.wert = ws.a2 WHERE we.inhalt = ['Standardsprache']")
        ->werte($standardid, $a2, $standardsprache);

  $bezeichnung  = "";
  $pfad         = "";

  if($id !== null) {
    $DBS->anfrage("SELECT {wsd.bezeichnung}, {wsd.pfad} FROM website_seitendaten as wsd JOIN website_seiten as ws ON wsd.seite = ws.id WHERE ws.id = ? AND wsd.sprache = ?", "ii", $id, $standardid)
          ->werte($bezeichnung, $pfad);
  }

  $formular[] = new UI\Formularfeld(new UI\Ueberschrift("3", "$standardsprache:"));
  $formular[] = new UI\FormularFeld(new UI\InhaltElement("Bezeichnung:"),   (new UI\Textfeld("{$idpre}Bezeichnung$a2")) ->setWert($bezeichnung));
  $formular[] = new UI\FormularFeld(new UI\InhaltElement("Pfadabschnitt:"), (new UI\Textfeld("{$idpre}Pfad$a2"))        ->setWert($pfad));

  $anf      = $DBS->anfrage("SELECT id, {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')) as bezeichnung FROM website_sprachen WHERE id != ?", "i", $standardid);
  $sprachen = [$a2];

  while($anf->werte($sprachid, $a2, $name)) {
    $bezeichnung  = "";
    $pfad         = "";

    if($id !== null) {
      $DBS->anfrage("SELECT {wsd.bezeichnung}, {wsd.pfad} FROM website_seitendaten as wsd JOIN website_seiten as ws ON wsd.seite = ws.id WHERE wsd.sprache = ?", "i", $sprachid)
            ->werte($bezeichnung, $pfad);
    }

    $formular[] = new UI\Formularfeld(new UI\Ueberschrift("3", "$name:"));
    $formular[] = (new UI\FormularFeld(new UI\InhaltElement("Bezeichnung:"),   (new UI\Textfeld("{$idpre}Bezeichnung$a2")) ->setWert($bezeichnung)))->setOptional(true);
    $formular[] = (new UI\FormularFeld(new UI\InhaltElement("Pfadabschnitt:"), (new UI\Textfeld("{$idpre}Pfad$a2"))        ->setWert($pfad)))->setOptional(true);
    $sprachen[] = $a2;
  }

  if($id === null) {
    $formular[] = (new UI\Knopf("Neue Seite anlegen", "Erfolg"))            ->setSubmit(true);
    $formular   ->addSubmit("website.verwaltung.seiten.neu.speichern($zugehoerig)");
  } else {
    $formular[] = (new UI\Knopf("Änderungen speichern", "Erfolg"))          ->setSubmit(true);
    $formular   ->addSubmit("website.verwaltung.seiten.bearbeiten.speichern($id)");
  }

  $spalte[]   = $formular;
  $spalte[]   = new UI\VerstecktesFeld("{$idpre}Sprachen", join(";", $sprachen));
  $fenster[]  = new UI\Zeile($spalte);

  return new UI\Fenster($idpre, $fenstertitel, new UI\Zeile($spalte));
}

?>