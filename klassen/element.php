<?php

namespace Website;

use UI;

abstract class Element extends UI\Element {
  protected $tag = "div";

  protected const KEIN_INHALT = "Kein Inhalt";
  protected const GELOESCHT = "Gelöscht";

  /** @var int $id ID des Elementes */
  protected $eid;
  /** @var string $sprache A2-Kennung der Sprache */
  protected $sprache;
  /** @var string $version Version des Elementes (alt; aktuell; neu) */
  protected $version;
  /** @var string $modus Betrachtungsmodus (sehen; bearbeiten) */
  protected $modus;
  /** @var string $status Status des Elements */
  protected $status;
  /** @var array FELDER Felder, die JS sammeln und in die Anfrage packen soll
   * [DB-Feld => HTML Element ID]
   */
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
    if ($id !== null) {
      /** @var \Kern\DB $DBS */
      global $DBS;

      $DBS->anfrage("SELECT {a2} FROM website_sprachen WHERE id = (SELECT sprache FROM website__{$this->tabelle} WHERE id = ?)", "i", $id)
            ->werte($this->sprache);
      $DBS->anfrage("SELECT status$version, position FROM website__{$this->tabelle} WHERE id = ?", "i", $this->eid)
            ->werte($this->status, $this->position);
    }
    $this->version  = $version;
    $this->modus    = $modus;
    $this->laden();
  }

  /**
   * Gibt die Felder zurück, die JS sammen soll
   * @return array
   */
  public function getFelder(): array {
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
   * Gibt das Element im Bearbeitungsfenster aus
   * @param string $idpre Stamm der IDs, welcher zu Beginn jeder Eingabe vorkommen sollte
   * Dient zur Unterscheidung zwischen mehreren Fenstern
   * @return [Text => Element] Die Elemente, die für das Bearbeiten des Formulares notwendig sind.
   * Ist `Text` ein Integer, so wird dieser weggelassen, und das Element nimmt die gesamte Breite im Formular an
   */
  public abstract function bearbeiten($idpre): array;

  /**
   * Gibt zusätzliche Inhalte zurück, welche **unter** der Haupttabelle im Bearbeitungsfenster sind, aus
   *
   * @param string $idpre Stamm der IDs, welcher zu Beginn jeder Eingabe vorkommen sollte
   * Dient zur Unterscheidung zwischen mehreren Fenstern
   * @return UI\Element[]
   */
  public function bearbeitenPost($idpre): array {
    return [];
  }

  /**
   * Gibt den Knopf zurück, welcher in der "Neues Element" Auswahl sichtbar ist
   *
   * @return UI\Knopf
   */
  public abstract static function genNeuKnopf(): UI\Knopf;

  /**
   * Prüft, ob das Element Inhalt sichtbar ist.
   * @return bool
   */
  public function anzeigen(): bool {
    /** @var \Kern\DB $DBS */
    global $DBS;
    $DBS->anfrage("SELECT status{$this->version} FROM website__{$this->tabelle} WHERE id = ?", "i", $this->eid)
    ->werte($status);
    return $status == "a";
  }

  /**
   * Gibt das Element als gültigen HTML-Code <b>zum Betrachten auf der Website, nicht Bearbeiten</b> zurück
   * @return string
   */
  public function __toString(): string {
    return parent::__toString();
  }

  /**
   * Wird aufgerufen, nachdem das Element gespeichert worden ist.
   * Um zusätzliche Daten zu speichern.
   */
  public function nachSpeichern() {

  }

  /**
   * Setzt die ID des Elements
   *
   * @param number $eid :)
   * @return self
   */
  public function setEId($eid) : self {
    $this->eid = $eid;
    return $this;
  }

  /**
   * @param  string $tabelle      Tabelle, in welcher sich die Daten befinden, siehe <code>ELEMENT</code> im readme.
   * @param  mixed ...$variablen  Die Variablen, welche gefüllt werden. Die Reihenfolge <b>MUSS</b> der Reihenfolge in <code>Website\Element::$FELDER</code> entsprechen. Die Spaltennamen müssen den <i>keys</i> des Arrays entsprechen und mit je "alt", "aktuell", und "neu" enden.
   */
  public function werteFuellen(&...$variablen) {
    global $DBS;
    $select = [];
    foreach ($this->felder as $spalte => $_) {
      $select[] = "{we.$spalte{$this->version}}";
    }
    $sql = "SELECT " . join(",", $select) . " FROM website__{$this->tabelle} as we WHERE we.id = ?";
    $DBS->anfrage($sql, "i", $this->eid)->werte(...$variablen);
  }
}
