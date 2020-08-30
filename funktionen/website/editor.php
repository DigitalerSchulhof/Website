<?php
namespace Website\Elemente;
use \Website\Element as Element;
use UI;

class Editor extends Element {
  protected $inhalt;

  protected $felder = array(
    "inhalt" => "Editor"
  );

  public function laden() {
    if($this->eid === null) {
      $this->inhalt = "";
    } else {
      global $DBS;
      $this->werteFuellen("editoren", $this->inhalt);
    }
  }

  public static function postValidieren() {
    global $inhalt;
    if(!UI\Check::istText($inhalt)) {
      Anfrage::addFehler(20);
    }
  }

  public function bearbeiten($idpre) : UI\Element {
    return (new UI\Editor("{$idpre}{$this->felder["inhalt"]}"))->setWert($this->inhalt);
  }

  public function __toString() : string {
    return "{$this->codeAuf()}{$this->inhalt}{$this->codeZu()}";
  }
}
?>