<?php

namespace Website\Elemente;

use \Website\Element as Element;
use UI;
use Anfrage;

class Editor extends Element {
  /** @var string */
  protected $inhalt;


  protected $felder = array(
    "inhalt"  => "Editor"
  );

  protected $tabelle = "editoren";

  public function laden() {
    if ($this->eid === null) {
      $this->inhalt = "";
    } else {
      $this->werteFuellen($this->inhalt);
    }
  }

  public static function postValidieren() {
    global $inhalt;
    if (!UI\Check::istEditor($inhalt)) {
      Anfrage::addFehler(20);
    }
  }

  public static function genNeuKnopf(): UI\Knopf {
    return new UI\GrossIconKnopf(new UI\Icon("fas fa-pencil-alt"), "Neuer Editor", "Standard");
  }

  public function bearbeiten($idpre): array {
    $editor = new UI\Editor("{$idpre}{$this->felder["inhalt"]}");
    $editor->setWert($this->inhalt);
    return [$editor];
  }

  public function __toString(): string {
    $inh = $this->inhalt;
    if ($this->modus == "bearbeiten") {
      if($this->status == "l") {
        return new UI\Notiz("Gelöschter Editor");
      }
      if($this->inhalt === null) {
        return new UI\Notiz("Editor ohne Inhalt");
      }
    }
    return "{$this->codeAuf()}$inh{$this->codeZu()}";
  }
}
