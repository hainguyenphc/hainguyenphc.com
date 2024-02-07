<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\sm_test\SmTestMessage;
use Drupal\sm_test\SmTestServiceMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Tests the synchronous (sync://) transport.
 */
final class SmSynchronousTransportTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm',
    'sm_test',
    'sm_test_transport',
  ];

  /**
   * @var null|array
   */
  private ?array $messageToSendersMapping = NULL;

  /**
   * Tests the message is handled synchronously (immediately)
   */
  public function testSynchronousTransport(): void {
    $this->setRouting([
      SmTestMessage::class => 'sm.transport.synchronous',
      // Everything else will be asynchronous.
      // This asynchronous transport is set otherwise messenger routes unmatched
      // message as synchronous.
      '*' => 'sm_test_transport__in_memory',
    ]);

    $synchronousMessage = new SmTestMessage();
    $this->bus()->dispatch($synchronousMessage);
    $asynchronousMessage = new SmTestServiceMessage();
    $this->bus()->dispatch($asynchronousMessage);

    // Handled since its async.
    static::assertNotNull($synchronousMessage->handledBy);
    // This one isn't handled yet.
    static::assertNull($asynchronousMessage->handledBy);
  }

  /**
   * Default messenger bus.
   */
  private function bus(): MessageBusInterface {
    return \Drupal::service(MessageBusInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    parent::register($container);

    if (NULL !== $this->messageToSendersMapping) {
      $container->setParameter('sm.routing', $this->messageToSendersMapping);
    }
  }

  /**
   * Sets routing and triggers a rebuild.
   *
   * @phpstan-param array<string|class-string, string|string[]> $messageToSendersMapping
   */
  private function setRouting(array $messageToSendersMapping): void {
    $this->messageToSendersMapping = $messageToSendersMapping;
    \Drupal::service('kernel')->rebuildContainer();
  }

}
