<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm_config\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sm_test\SmTestMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;

/**
 * Tests senders locator with config.
 */
final class SmConfigTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm',
    'sm_config',
    'sm_test',
    'sm_test_transport',
  ];

  /**
   * Tests senders locator.
   */
  public function testSendersLocator(): void {
    // Test no config:
    $this->setRoutingConfig([]);
    $sendersLocator = \Drupal::getContainer()->get(SendersLocatorInterface::class);
    static::assertInstanceOf(SendersLocator::class, $sendersLocator);
    $envelope = new Envelope(new SmTestMessage());
    $senders = iterator_to_array($sendersLocator->getSenders($envelope));
    static::assertCount(0, $senders);

    // Test exact message and fallback:
    $this->setRoutingConfig([
      [
        'bus' => 'sm.bus.default',
        'message' => '*',
        'receivers' => ['sm_test_transport__in_memory'],
      ],
      [
        'bus' => 'sm.bus.default',
        'message' => SmTestMessage::class,
        'receivers' => ['sm_test_transport__in_memory2'],
      ],
    ]);
    $sendersLocator = \Drupal::getContainer()->get(SendersLocatorInterface::class);
    static::assertInstanceOf(SendersLocator::class, $sendersLocator);
    $envelope = new Envelope(new SmTestMessage());
    $senders = iterator_to_array($sendersLocator->getSenders($envelope));
    static::assertArrayHasKey('sm_test_transport__in_memory2', $senders);

    // Test fallback only:
    $this->setRoutingConfig([
      [
        'bus' => 'sm.bus.default',
        'message' => '*',
        'receivers' => ['sm_test_transport__in_memory'],
      ],
    ]);
    $sendersLocator = \Drupal::getContainer()->get(SendersLocatorInterface::class);
    static::assertInstanceOf(SendersLocator::class, $sendersLocator);
    $envelope = new Envelope(new SmTestMessage());
    $senders = iterator_to_array($sendersLocator->getSenders($envelope));
    static::assertArrayHasKey('sm_test_transport__in_memory', $senders);
  }

  /**
   * Sets routing config and triggers a rebuild.
   */
  private function setRoutingConfig(array $config): void {
    $this
      ->config('sm_config.settings')
      ->set('routing', $config)
      ->save();
    \Drupal::service('kernel')->rebuildContainer();
  }

}
