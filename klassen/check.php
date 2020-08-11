<?php
namespace Proto;

class Check {
  /**
   * Prüft, ob der übergebene String mit »pizza« anfängt
   * @param  string $pizza :)
   * @return bool
   */
  public static function istPizza($pizza) : bool {
    return preg_match("/^pizza.*/i", $pizza) === 1;
  }
}

?>