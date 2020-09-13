<?php
namespace Website;
use UI;

abstract class Element extends UI\Element {
  protected $tag = "div";

  protected const KEIN_INHALT = "Kein Inhalt oder gelöscht";

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
  /** @var string Die Tabelle, in welcher sich die Inhalte befinden. (Siehe: Readme) */
  protected $tabelle;
  /**
   * Erzeugt ein neues Websiteelement und lädt dessen Daten
   * @param int $id         ID des Elementes
   * @param string $version Version des Elementes (alt; aktuell; neu)
   * @param string $modus   Betrachtungsmodus (sehen; bearbeiten)
   * <i>Ist momentan nich in Gebrauch, jedoch der Vollständigeit halber, und für eventuellen künftigen Gebrauch, im Code gelassen</i>
   * @param string|null $sprache A2-Kennung der Sprache
   * Wenn <code>$id !== null</code>: Automatisch geladen
   */
  public function __construct($id, $version, $modus, $sprache = null) {
    parent::__construct();
    $this->eid      = $id;
    if($id !== null) {
      global $DBS;
      $DBS->anfrage("SELECT {a2} FROM website_sprachen WHERE id = (SELECT sprache FROM website__{$this->tabelle} WHERE id = ?)", "i", $id)
            ->werte($sprache);
    }
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
   * @param  string $tabelle      Tabelle, in welcher sich die Daten befinden, siehe <code>ELEMENT</code> im readme.
   * @param  mixed ...$variablen  Die Variablen, welche gefüllt werden. Die Reihenfolge <b>MUSS</b> der Reihenfolge in <code>Website\Element::$FELDER</code> entsprechen. Die Spaltennamen müssen den <i>keys</i> des Arrays entsprechen und mit je "alt", "aktuell", und "neu" enden.
   */
  public function werteFuellen(&...$variablen) {
    global $DBS;
    $select = [];
    foreach($this->felder as $spalte => $_) {
      $select[] = "{we.$spalte{$this->version}}";
    }
    $sql = "SELECT ".join(",", $select)." FROM website__{$this->tabelle} as we WHERE we.id = ?";
    $DBS->anfrage($sql, "i", $this->eid)->werte(...$variablen);
  }
}