<?php

namespace Drupal\Tests\eg_tests\Kernel;

use Drupal\KernelTests\KernelTestBase;

class MyServiceTest extends KernelTestBase {

  /**
   * The service under test.
   * 
   * @var \Drupal\eg_tests\MyServiceInterface
   */
  protected $myService;

  /** 
   * The modules to load to run the test.
   * 
   * @var array
   */
  public static $modules = [
    'eg_tests',
    'service_test',
  ];

  /** 
   * {@inheritDoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig([
      'eg_tests',
    ]);
    $this->myService = \Drupal::service('eg_tests.status_text');
  }

  public function testLabel() {
    $label = $this->myService->getLabel('hainguyen');
    $this->assertTrue($label === 'Hai Nguyen', $label);
  }

  public function testMessage() {
    $message = $this->myService->getMessage('hainguyen');
    $this->assertTrue($message === 'Hai Nguyen - Web Developer @ Northern Commerce', $message);
  }

  public function testNotFound() {
    $label = $this->myService->getLabel('doesnt_exist_key');
    $this->assertFalse($label);

    $message = $this->myService->getMessage('doesnt_exist_key');
    $this->assertFalse($message);
  }

}
