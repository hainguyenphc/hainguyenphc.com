<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\sm_test\SmTestMessage;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;

/**
 * Tests senders locator.
 *
 * Tests 'messenger.senders_locator' service.
 *
 * @covers \Symfony\Component\Messenger\Transport\Sender\SendersLocator
 * @covers \Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface
 * @covers \Drupal\sm\SmCompilerPass::sendersLocator
 */
final class SmSendersLocatorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm',
    'sm_test_transport',
  ];

  /**
   * @var null|array
   */
  private ?array $messageToSendersMapping = NULL;


  /**
   * @var null|array<string, array<mixed>>
   */
  private ?array $transportsParameter = NULL;

  /**
   * Tests senders locator.
   */
  public function testSendersLocator(): void {
    // Empty locator.
    $definition = $this->container->getDefinition(SendersLocatorInterface::class);
    static::assertEquals([], $definition->getArgument(0));

    // This can be a Reference or Definition somehow depending on the transports
    // in the system. Not sure why. Maybe quantity?
    $arg1 = $definition->getArgument(1);
    static::assertInstanceOf(Reference::class, $arg1);
    $serviceLocator = $this->container->get((string) $arg1);
    static::assertInstanceOf(ServiceLocator::class, $serviceLocator);
    static::assertEquals([
      'sm.transport.synchronous',
      'sm_test_transport.transport.sm_test_transport__in_memory',
      'sm_test_transport.transport.sm_test_transport__in_memory2',
      'sm_test_transport__in_memory',
      'sm_test_transport__in_memory2',
      'synchronous',
    ], array_keys($serviceLocator->getProvidedServices()));

    $sendersLocator = \Drupal::getContainer()->get(SendersLocatorInterface::class);
    static::assertInstanceOf(SendersLocator::class, $sendersLocator);
    $envelope = new Envelope(new SmTestMessage());
    $senders = iterator_to_array($sendersLocator->getSenders($envelope));
    static::assertCount(0, $senders);

    // Fallback locator.
    $this->setRouting(['*' => 'sm_test_transport__in_memory']);
    $definition = $this->container->getDefinition(SendersLocatorInterface::class);
    static::assertEquals([
      '*' => ['sm_test_transport__in_memory'],
    ], $definition->getArgument(0));

    $arg1 = $definition->getArgument(1);
    static::assertInstanceOf(Reference::class, $arg1);
    $serviceLocator = $this->container->get((string) $arg1);
    static::assertInstanceOf(ServiceLocator::class, $serviceLocator);
    static::assertEquals([
      'sm.transport.synchronous',
      'sm_test_transport.transport.sm_test_transport__in_memory',
      'sm_test_transport.transport.sm_test_transport__in_memory2',
      'sm_test_transport__in_memory',
      'sm_test_transport__in_memory2',
      'synchronous',
    ], array_keys($serviceLocator->getProvidedServices()));

    $sendersLocator = \Drupal::getContainer()->get(SendersLocatorInterface::class);
    static::assertInstanceOf(SendersLocator::class, $sendersLocator);
    $envelope = new Envelope(new SmTestMessage());
    $senders = iterator_to_array($sendersLocator->getSenders($envelope));
    static::assertCount(1, $senders);
    static::assertArrayHasKey('sm_test_transport__in_memory', $senders);
    static::assertInstanceOf(InMemoryTransport::class, $senders['sm_test_transport__in_memory']);
  }

  /**
   * Tests matching on partial class name.
   *
   * This is functionality provided by Symfony Messenger.
   */
  public function testSendersLocatorPartialClassMatch(): void {
    $this->setRouting([
      'Drupal\sm_test\*' => 'sm_test_transport__in_memory2',
      '*' => 'sm_test_transport__in_memory',
    ]);

    $sendersLocator = \Drupal::getContainer()->get(SendersLocatorInterface::class);
    $envelope = new Envelope(new SmTestMessage());
    $senders = iterator_to_array($sendersLocator->getSenders($envelope));
    static::assertCount(1, $senders);
    static::assertArrayHasKey('sm_test_transport__in_memory2', $senders);
  }

  /**
   * Tests matching on exact class name.
   *
   * This is functionality provided by Symfony Messenger.
   */
  public function testSendersLocatorExactClassMatch(): void {
    $this->setRouting([
      'Drupal\sm_test\SmTestMessage' => 'sm_test_transport__in_memory2',
      'Drupal\sm_test' => 'sm_test_transport__in_memory',
      '*' => 'sm_test_transport__in_memory',
    ]);

    $sendersLocator = \Drupal::getContainer()->get(SendersLocatorInterface::class);
    $envelope = new Envelope(new SmTestMessage());
    $senders = iterator_to_array($sendersLocator->getSenders($envelope));
    static::assertCount(1, $senders);
    static::assertArrayHasKey('sm_test_transport__in_memory2', $senders);
  }

  /**
   * Tests transports registered via `sm.transports` parameter.
   */
  public function testCustomTransportsParameter(): void {
    $this->setCustomTransports([
      'my_transport' => [
        'dsn' => 'in-memory://?serialize=true',
        'options' => [],
      ],
      'my_transport2' => [
        'dsn' => 'in-memory://?serialize=true',
        'options' => [],
      ],
    ]);

    $definition = $this->container->getDefinition(SendersLocatorInterface::class);
    $arg1 = $definition->getArgument(1);
    static::assertInstanceOf(Reference::class, $arg1);
    $serviceLocator = $this->container->get((string) $arg1);
    static::assertInstanceOf(ServiceLocator::class, $serviceLocator);
    $providedServices = $serviceLocator->getProvidedServices();
    static::assertArrayHasKey('sm.transport.my_transport', $providedServices);
    static::assertArrayHasKey('sm.transport.my_transport2', $providedServices);
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    // This method can be invoked again when calling kernel->rebuildContainer
    // a strategy borrowed from `DrupalFlushAllCachesTest`.
    parent::register($container);

    if (NULL !== $this->messageToSendersMapping) {
      $container->setParameter('sm.routing', $this->messageToSendersMapping);
    }

    if (NULL !== $this->transportsParameter) {
      $container->setParameter('sm.transports', $this->transportsParameter);
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

  /**
   * Sets custom transports and triggers a rebuild.
   *
   * @phpstan-param array<string, array<mixed>> $transports
   */
  private function setCustomTransports(array $transports): void {
    $this->transportsParameter = $transports;
    \Drupal::service('kernel')->rebuildContainer();
  }

}
