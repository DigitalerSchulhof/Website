<?php
global $DSH_URLGANZ;
if(preg_match("/^Blog\//", $DSH_URLGANZ) === 1) {
  $ANGEBOT = new UI\MiniIconKnopf(new UI\Icon(UI\Konstanten::DRUCKEN), "Seite Drucken");
}

?>