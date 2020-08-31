<?php
namespace Website;
use UI;

abstract class Element extends UI\Element {
  protected $tag = "div";

  /** @var int $id ID des Elementes */
  protected $eid;
  /** @var string $sprache A2-Kennung der Sprache */
  protected $sprache;
  /** @var string $version Version des Elementes (alt; aktuell; neu) */
  protected $version;
  /** @var string $modus Betrachtungsmodus (sehen; bearbeiten) */
  protected $modus;
  /** @var array FELDER Felder, die JS sammeln und in die Anfrage packen soll */
  protected $felder;

  /**
   * Erzeugt ein neues Websiteelement und lädt dessen Daten
   * @param int $id         ID des Elementes
   * @param string $sprache A2-Kennung der Sprache
   * @param string $version Version des Elementes (alt; aktuell; neu)
   * @param string $modus   Betrachtungsmodus (sehen; bearbeiten)
   * <i>Ist momentan nich in Gebrauch, jedoch der Vollständigeit halber, und für eventuellen künftigen Gebrauch, im Code gelassen</i>
   */
  public function __construct($id, $sprache, $version, $modus) {
    parent::__construct();
    $this->eid      = $id;
    $this->sprache  = $sprache;
    $this->version  = $version;
    $this->modus    = $modus;
    $this->laden();
  }

  /**
   * Gibt die Felder zurück, die JS sammen soll
   * @return array
   */
  public function getFelder() : array {
    return $this->felder;
  }

  /**
   * Prüft, ob alle übergebenen Felder gültig sind und gibt ggf. Fehler aus
   */
  public static abstract function postValidieren();

  /**
   * Lädt die Daten des Elements in die korrekten Variablen
   */
  public abstract function laden();

  /**
   * Gibt das Element im Bearbeitungs-Modus aus
   * @param string $idpre Stamm der IDs, welcher zu Beginn jeder Eingabe vorkommen sollte
   * Dient zur Unterscheidung zwischen mehreren Fenstern
   * @return UI\Element
   */
  public abstract function bearbeiten($idpre) : UI\Element;

  /**
   * Prüft, ob das Element Inhalt hat und angezeigt werden kann.
   * @return bool
   */
  public abstract function anzeigen() : bool;

  /**
   * Gibt das Element als gültigen HTML-Code <b>zum Betrachten auf der Website, nicht Bearbeiten</b> zurück
   * @return string
   */
  public function __toString() : string {
    return parent::__toString();
  }


  /**
   * Füllt die Variablen mit Inhalten aus der Datenbank
   * @param  string $tabelle      Tabelle, in welcher sich die Daten befinden, siehe <code>ELEMENT</code> im readme.
   * @param  mixed ...$variablen  Die Variablen, welche gefüllt werden. Die Reihenfolge <b>MUSS</b> der Reihenfolge in <code>Website\Element::$FELDER</code> entsprechen. Die Spaltennamen müssen den <i>keys</i> des Arrays entsprechen und mit je "alt", "aktuell", und "neu" enden.
   */
  public function werteFuellen($tabelle, &...$variablen) {
    global $DBS;
    $select = [];
    foreach($this->felder as $spalte => $_) {
      $select[] = "IF(wei.$spalte{$this->version} IS NULL, (SELECT weii.$spalte{$this->version} FROM website_{$tabelle}inhalte as weii WHERE weii.element = we.id AND weii.sprache = (SELECT id FROM website_sprachen WHERE a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wei.$spalte{$this->version})";
    }
    $sql = "SELECT ".join(",", $select)." FROM website_$tabelle as we JOIN website_sprachen as ws LEFT JOIN website_{$tabelle}inhalte as wei ON wei.sprache = ws.id AND wei.element = we.id WHERE ws.a2 = [?] AND we.id = ?";
    $DBS->anfrage($sql, "si", $this->sprache, $this->eid)->werte(...$variablen);
  }
}