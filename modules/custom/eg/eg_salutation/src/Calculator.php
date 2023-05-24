<?php

namespace Drupal\eg_salutation;

class Calculator {

  protected $a;

  protected $b;

  public function __construct($a, $b) {
    $this->a = $a;
    $this->b = $b;
  }

  public function add() {
    return $this->a + $this->b;
  }

  public function subtract() {
    return $this->a - $this->b;
  }

}
