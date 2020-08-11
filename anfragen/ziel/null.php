<?php
if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(time() % 2 !== 0) {
  Anfrage::addFehler(0, true);
}

Anfrage::setTyp("Meldung");
Anfrage::setRueck("Meldung", new UI\Meldung("Hi!", "Hallo!", "Erfolg"));
?>
