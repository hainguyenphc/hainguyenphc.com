<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm_config\Functional;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests config form.
 *
 * @coversDefaultClass \Drupal\sm_config\Form\SmRoutingConfigForm
 */
final class SmConfigFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm',
    'sm_test',
    'sm_config',
    'sm_test_transport',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->drupalCreateUser([
      'administer sm_config configuration',
    ]));
  }

  /**
   * Tests config form with no messages.
   */
  public function testConfigFormNoMessagesDefined(): void {
    $this->moduleInstaller()->uninstall(['sm_test']);
    $this->drupalGet(Url::fromRoute('sm_config.settings'));
    $as = $this->assertSession();
    $as->responseNotContains('Class prefix');
    $as->responseContains('Fallback');
  }

  /**
   * Tests no transports enabled.
   */
  public function testConfigFormNoTransports(): void {
    $this->moduleInstaller()->uninstall(['sm_test_transport']);
    $this->moduleInstaller()->install(['sm_test_transport_override']);
    $this->drupalLogin($this->drupalCreateUser([
      'administer sm_config configuration',
    ]));
    $this->drupalGet(Url::fromRoute('sm_config.settings'));
    $as = $this->assertSession();
    $as->responseContains('There are no transports enabled.');
  }

  /**
   * Tests a config form with transports and messages.
   */
  public function testConfigFormNormal(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'administer sm_config configuration',
    ]));
    $this->drupalGet(Url::fromRoute('sm_config.settings'));
    $as = $this->assertSession();

    // All transports are present.
    $as->fieldExists('messages[sm.bus.default][fallback][*][sm_test_transport.transport.sm_test_transport__in_memory]');
    $as->fieldExists('messages[sm.bus.default][fallback][*][sm_test_transport.transport.sm_test_transport__in_memory2]');
    $as->fieldExists('messages[sm.bus.default][fallback][*][sm_test_transport__in_memory]');
    $as->fieldExists('messages[sm.bus.default][fallback][*][sm_test_transport__in_memory2]');

    // Class prefixes are present.
    $as->fieldExists('messages[sm.bus.default][prefix][Drupal\sm_test\*][sm_test_transport__in_memory]');
    $as->fieldExists('messages[sm.bus.default][prefix][Drupal\*][sm_test_transport__in_memory]');

    // Exact is present:
    $as->fieldExists('messages[sm.bus.default][message][Drupal\sm_test\SmTestMessage][sm_test_transport__in_memory]');
  }

  /**
   * Module installer.
   */
  private function moduleInstaller(): ModuleInstallerInterface {
    return \Drupal::service('module_installer');
  }

}
