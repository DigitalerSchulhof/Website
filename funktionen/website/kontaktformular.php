<?php

namespace Website\Elemente;

use \Website\Element as Element;
use UI;
use Anfrage;
use UI\Option;

class Kontaktformular extends Element {
  /** @var string */
  protected $betreff;
  /** @var string */
  protected $kopie;
  /** @var boolean */
  protected $anhang;
  /** @var string */
  protected $ansicht;

  /** @var array ["Name" => string, "Mail" => string, "Beschreibung" => string][] */
  protected $empfaenger;

  protected $felder = array(
    "betreff" => "Betreff",
    "kopie"   => "Kopie",
    "anhang"  => "Anhang",
    "ansicht" => "Ansicht"
  );

  protected $tabelle = "kontaktformulare";

  public function laden() {
    if ($this->eid === null) {
      $this->betreff    = "";
      $this->kopie      = "w";
      $this->anhang     = true;
      $this->ansicht    = "m";
      $this->empfaenger = [];
    } else {
      /** @var \Kern\DB $DBS */
      global $DBS;
      $this->werteFuellen($this->betreff, $this->kopie, $this->anhang, $this->ansicht);

      $anf = $DBS->anfrage("SELECT id, {name}, {mail}, {beschreibung} FROM website__kontaktformulare_empfaenger WHERE formular = ?", "i", $this->eid);
      $this->empfaenger = [];
      while ($anf->werte($id, $name, $mail, $beschreibung)) {
        $this->empfaenger[] = ["Id" => $id, "Name" => $name, "Mail" => $mail, "Beschreibung" => $beschreibung];
      }
    }
  }

  public static function postValidieren() {
    global $betreff, $kopie, $anhang, $ansicht, $empfaenger;
    Anfrage::post("empfaenger");
    $empfaenger = json_decode($empfaenger, true);
    if($empfaenger === null) {
      Anfrage::addFehler(-3, true);
    }
    if (!in_array($kopie, ["i", "w", "n"])) {
      Anfrage::addFehler(-3, true);
    }
    if (!UI\Check::istToggle($anhang)) {
      Anfrage::addFehler(-3, true);
    }
    if(!in_array($ansicht, ["m", "v"])) {
      Anfrage::addFehler(-3, true);
    }
    if (!UI\Check::istText($betreff)) {
      Anfrage::addFehler(23);
    }

    foreach($empfaenger as $e) {
      if(!isset($e["name"]) || !isset($e["mail"]) || !isset($e["beschreibung"])) {
        Anfrage::addFehler(-3, true);
      }
      if(!UI\Check::istName($e["name"])) {
        Anfrage::addFehler(24);
      }
      if(!UI\Check::istMail($e["mail"])) {
        Anfrage::addFehler(25);
      }
      if(!UI\Check::istText($e["beschreibung"], 0)) {
        Anfrage::addFehler(26);
      }
    }
  }

  /**
   * Erzeugt eine Empfängeransicht
   *
   * @param array $e Array mit Empfängerdaten. ["Name" => string, "Mail" => string, "Beschreibung" => string]
   * @return UI\Element
   */
  public static function genEmpfaenger($idpre, $e): UI\Element {
    $r = new UI\FormularTabelle();
    $r ->setId($idpre);
    $r[] = new UI\FormularFeld(new UI\InhaltElement("Name:"),           (new UI\Textfeld("{$idpre}Name"))->setWert($e["Name"]));
    $r[] = new UI\FormularFeld(new UI\InhaltElement("eMail-Adresse:"),  (new UI\Mailfeld("{$idpre}Mail"))->setWert($e["Mail"]));
    $r[] = new UI\FormularFeld(new UI\InhaltElement("Beschreibung:"),   (new UI\Textarea("{$idpre}Beschreibung"))->setWert($e["Beschreibung"]));
    $r[] = new UI\FormularFeld((new UI\IconKnopf(new UI\Icon(UI\Konstanten::LOESCHEN), "Empfänger löschen", "Fehler"))->addFunktion("onclick", "website.element.kontaktformulare.empfaenger.loeschen(this)")->setStyle("float", "right"));
    return $r;
  }

  public static function genNeuKnopf(): UI\Knopf {
    return new UI\GrossIconKnopf(new UI\Icon("fas fa-paper-plane"), "Neues Kontaktformular", "Standard");
  }

  public function bearbeiten($idpre): array {
    $betreff = new UI\Textfeld("{$idpre}{$this->felder["betreff"]}");
    $betreff ->setWert($this->betreff);

    $kopie = new UI\Auswahl("{$idpre}{$this->felder["kopie"]}", $this->kopie);
    $kopie[] = new Option("Immer",          "i");
    $kopie[] = new Option("Selbst wählbar", "w");
    $kopie[] = new Option("Nie",            "n");

    $anhang = new UI\Schieber("{$idpre}{$this->felder["anhang"]}");
    $anhang ->setWert($this->anhang);

    $ansicht = new UI\Auswahl("{$idpre}{$this->felder["ansicht"]}", $this->ansicht);
    $ansicht[] = new Option("Menü",           "m");
    $ansicht[] = new Option("Visitenkarten",  "v");


    return ["Betreff:" => $betreff, "Kopie an den Absender:" => $kopie, "Anhänge erlauben:" => $anhang, "Ansicht:" => $ansicht];
  }

  public function bearbeitenPost($idpre): array {
    $b = new UI\Box();
    foreach($this->empfaenger as $i => $e) {
      $b[] = (self::genEmpfaenger("{$idpre}Empfaenger$i", $e))->setStyle("margin-bottom", "10px");
    }
    $b[] = (self::genEmpfaenger("{$idpre}EmpfaengerNeu", ["Name" => "", "Mail" => "", "Beschreibung" => ""]))->setStyle("margin-bottom", "10px")->setStyle("display", "none");
    $r = [new UI\Ueberschrift(2, "Empfänger:"), $b];
    $r[] = (new UI\IconKnopf(new UI\Icon(UI\Konstanten::NEU), "Empfänger hinzufügen", "Erfolg"))->addFunktion("onclick", "website.element.kontaktformulare.empfaenger.neu('$idpre')");
    return $r;
  }

  public function nachSpeichern() {
    /** @var \Kern\DB $DBS */
    global $DBS, $empfaenger;
    parent::nachSpeichern();
    $DBS->anfrage("DELETE FROM website__{$this->tabelle}_empfaenger WHERE formular = ?", "i", $this->eid);
    foreach($empfaenger as $e) {
      $DBS->neuerDatensatz("website__{$this->tabelle}_empfaenger", array("formular" => "?", "name" => "[?]", "mail" => "[?]", "beschreibung" => "[?]"), "isss", $this->eid, ...array_values($e));
    }
  }

  public function __toString(): string {
    if ($this->modus == "bearbeiten") {
      if ($this->status == "l") {
        return new UI\Notiz(self::GELOESCHT);
      }
    }
    $form = new UI\FormularTabelle();

    if(count($this->empfaenger) > 0) {
      $empfaengerauswahl = new UI\Auswahl("dshWebsiteKontaktformular{$this->position}Empfaenger");
      if(count($this->empfaenger) == 1) {
        $empfaengerauswahl->setDisabled(true);
        $empfaengerauswahl->setWert($this->empfaenger[0]["Id"]);
      }
      foreach($this->empfaenger as $e) {
        $n = $e["Name"];
        if(strlen($e["Beschreibung"])) {
          $n .= " - {$e["Beschreibung"]}";
        }
        $empfaengerauswahl[] = new UI\Option($n, $e["Id"]);
      }
    } else {
      $empfaengerauswahl = (new UI\Notiz("Keine Empfänger hinterlegt"))->setID("dshWebsiteKontaktformular{$this->position}Empfaenger");
    }

    $form[] = new UI\FormularFeld(new UI\InhaltElement("Empfänger:"), $empfaengerauswahl);
    $form[] = new UI\FormularFeld(new UI\InhaltElement("Name:"), new UI\Textfeld("dshWebsiteKontaktformular{$this->position}Name"));
    $form[] = new UI\FormularFeld(new UI\InhaltElement("eMail-Adresse:"), new UI\Mailfeld("dshWebsiteKontaktformular{$this->position}Mail"));
    $form[] = new UI\FormularFeld(new UI\InhaltElement("Betreff:"), new UI\Textfeld("dshWebsiteKontaktformular{$this->position}Betreff"));
    $form[] = new UI\FormularFeld(new UI\InhaltElement("Nachricht:"), new UI\Textarea("dshWebsiteKontaktformular{$this->position}Nachricht"));
    $form[] = new UI\FormularFeld(new UI\InhaltElement("Spamverhinderung:"), new UI\Spamschutz("dshWebsiteKontaktformular{$this->position}Spamschutz", 7));
    $form[] = (new UI\IconKnopf(new UI\Icon("fas fa-paper-plane"), "Absenden", "Erfolg"))->addFunktion("onclick", "website.element.kontaktformulare.senden({$this->position})");
    return $form;
  }
}
