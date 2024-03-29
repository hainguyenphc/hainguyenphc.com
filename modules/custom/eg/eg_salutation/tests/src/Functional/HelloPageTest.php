<?php

namespace Drupal\Test\eg_salutation\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the `eg_salutation.hello_ws` route.
 * 
 * @group eg_salutation
 */
class HelloPageTest extends BrowserTestBase {

  /** 
   * {@inheritDoc}
   */
  protected static $modules = ['eg_salutation', 'user'];

  /**
   * {@inheritDoc}
   */
  protected $defaultTheme = 'stable';

  /** 
   * Tests the main `eg_salutation.hello_ws` route.
   */
  public function testPage() {
    $expected = $this->assertDefaultSalutation();
    $config = $this->config('hello_world.custom_salutation');
    $config->set('salutation', 'Testing salutation');
    $config->save();

    $this->drupalGet('/hello');
    $this->assertSession()->pageTextNotContains($expected);
    $expected = 'Testing salutation';
    $this->assertSession()->pageTextContains($expected);
  }

  /** 
   * Tests that the configuration form for overriding the message works.
   */
  public function testSalutationOverrideForm() {
    $expected = $this->assertDefaultSalutation();
    $this->drupalGet('/admin/config/salutation-configuration');
    $this->assertSession()->statusCodeEquals(403);
    $account = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($account);
    $this->drupalGet('/admin/config/salutation-configuration');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Salutation configuration');
    $this->assertSession()->elementExists('css', '#edit-salutation');

    $edit = [
      'salutation' => 'My custom salutation',
    ];

    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('The configurationoptions have been saved');
    $this->drupalGet('/hello');
    $this->assertSession()->pageTextNotContains($expected);
    $this->assertSession()->pageTextContains('My custom salutation');
  }

  protected function assertDefaultSalutation() {
    $this->drupalGet('/hello');
    $this->assertSession()->pageTextContains('Our first route');
    $time = new \DateTime();
    $expected = '';
    
    $hour = (int) $time->format('G');
    if ($hour >= 00 && $hour < 12) {
      $expected = 'Good morning';
    }
    if ($hour >= 12 && $hour < 18) {
      $expected = 'Good afternoon';
    }
    if ($hour >= 18) {
      $expected = 'Good evening';
    }
    
    $expected .= ' world';
    
    $this->assertSession()->pageTextContains($expected);
    
    return $expected;
  }

}
