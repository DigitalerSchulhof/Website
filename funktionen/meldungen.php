<?php
switch ($meldeid) {
  case 0:
    Anfrage::setRueck("Meldung", new UI\Meldung("Änderungen erfolgreich!", "Die Änderungen an den Sprachen wurden vorgenomen.", "Erfolg"));
    break;
}
?>
