<?php
namespace Website\Elemente;
use \Website\Element as Element;
use UI;

class Editor extends Element {
  protected $inhalt;


  protected $felder = array(
    "inhalt" => "Editor"
  );

  protected $tabelle = "editoren";

  public function laden() {
    if($this->eid === null) {
      $this->inhalt = "";
    } else {
      $this->werteFuellen($this->inhalt);
    }
  }

  public static function postValidieren() {
    global $inhalt;
    if(!UI\Check::istEditor($inhalt)) {
      \Anfrage::addFehler(20);
    }
  }

  public function anzeigen() : bool {
    return $this->modus == "bearbeiten" || $this->inhalt !== null;
  }

  public function bearbeiten($idpre) : UI\Element {
    $editor = new UI\Editor("{$idpre}{$this->felder["inhalt"]}");
    $editor ->setWert($this->inhalt);
    return $editor;
  }

  public function __toString() : string {
    $inh = $this->inhalt;
    if($this->modus == "bearbeiten" && $this->inhalt === null) {
      $inh = new UI\Notiz(self::KEIN_INHALT);
    }
    return "{$this->codeAuf()}$inh{$this->codeZu()}";
  }
}
?>