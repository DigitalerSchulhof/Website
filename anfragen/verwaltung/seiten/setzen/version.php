<?php
Anfrage::post("id", "sprache", "version");
if(!Kern\Check::angemeldet()) {
  Anfrage::addFehler(-2, true);
}

if(!in_array($version, ["a", "n"])) {
  Anfrage::addFehler(-3, true);
}

if(!$DBS->existiert("website_sprachen", "a2 = [?]", "s", $sprache)) {
  Anfrage::addFehler(-3, true);
}

if(!UI\Check::istZahl($id) || !$DBS->existiert("website_seiten", $id)) {
  Anfrage::addFehler(-3, true);
}

if($version == "a") {
  if (!$DSH_BENUTZER->hatRecht("website.inhalte.versionen.alt.aktivieren")) {
    Anfrage::addFehler(-4, true);
  }
} else {
  if (!$DSH_BENUTZER->hatRecht("website.inhalte.versionen.neu.aktivieren")) {
    Anfrage::addFehler(-4, true);
  }
}

// Alle Elemente sammeln
new Kern\Wurmloch("funktionen/website/elemente.php", array(), function($r) use ($id, $sprache, $version) {
  global $DBS;
  foreach($r as $el => $kl) {
    $sql = [];
    $kl = new $kl(null, null, null, null);
    if($version == "a") {
      foreach($kl->getFelder() as $sp => $_) {
        $sql[] = "{$sp}aktuell = {$sp}alt";
      }
    } else {
      foreach($kl->getFelder() as $sp => $_) {
        $sql[] = "{$sp}alt = {$sp}aktuell, {$sp}aktuell = {$sp}neu";
      }
    }
    $DBS->anfrage("UPDATE website_{$el}inhalte JOIN website_$el SET ".join(",", $sql)." WHERE seite = ? AND sprache = (SELECT id FROM website_sprachen WHERE a2 = [?])", "is", $id, $sprache);
  }
});

?>