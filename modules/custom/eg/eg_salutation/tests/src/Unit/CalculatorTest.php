<?php

namespace Drupal\Tests\eg_salutation\Unit;

use Drupal\eg_salutation\Calculator;
use Drupal\Tests\UnitTestCase;

/** 
 * Tests the Calculator class methods.
 * 
 * @group eg_salutation
 */
class CalculatorTest extends UnitTestCase {

  /** 
   * Tests the Calculator::add() method.
   */
  public function testAdd() {
    $calculator = new Calculator(10, 5);
    $this->assertEquals($calculator->add(), 15);
  }

  /** 
   * Tests the Calculator::subtract() method.
   */
  public function testSubtract() {
    $calculator = new Calculator(10, 5);
    $this->assertEquals($calculator->subtract(), 5);
  }

}
