<?php
namespace Website;
use UI;

class Sprachwahl extends UI\Auswahl {
  public function __construct($id, $wert = null, $aktion = null) {
    global $DBS;
    if($wert === null) {
      $wert = \Kern\Einstellungen::laden("Website", "Standardsprache");
    }
    parent::__construct($id, $wert);
    $anf = $DBS->anfrage("SELECT {a2}, IF(namestandard = [''], {name}, CONCAT({name}, ' (', {namestandard}, ')')) as bezeichnung FROM website_sprachen");
    while($anf->werte($a2, $bez)) {
      parent::add($bez, $a2, $wert == $a2);
    }
    $this->addKlasse("dshUiEingabefeldKlein");
    $this->setStyle("float", "right");
    if($aktion !== null) {
      $this->addFunktion("oninput", $aktion);
    }
  }

  public function add($text, $wert, $selected = false) {
    throw new \Exception("Nicht implementiert");
  }
}

?>