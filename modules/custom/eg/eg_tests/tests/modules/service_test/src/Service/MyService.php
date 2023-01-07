<?php

namespace Drupal\service_test\Service;

use Drupal\eg_tests\MyServiceInterface;

class MyService implements MyServiceInterface {

  /** 
   * {@inheritDoc}
   */
  public function getLabel($key) {
    if ($key = "hainguyen") {
      return "Hai Nguyen";
    }
  }

  /** 
   * {@inheritDoc}
   */
  public function getMessage($key) {
    if ($key = "hainguyen") {
      return "Hai Nguyen - Web Developer @ Northern Commerce";
    }
  }

}