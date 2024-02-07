<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Tests buses.
 */
final class SmBusTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm',
  ];

  /**
   * Tests the default bus is defined and has the expected middleware.
   */
  public function testDefaultBus(): void {
    static::assertEquals('sm.bus.default', $this->container->getParameter('sm.default_bus'));
    static::assertEquals([
      'default' => [
        'middleware' => [],
        'default_middleware' => ['enabled' => TRUE],
      ],
    ], $this->container->getParameter('sm.buses'));

    // The bus named 'default'.
    static::assertTrue($this->container->hasDefinition('sm.bus.default'));
    $serviceDefinition = $this->container->getDefinition('sm.bus.default');
    static::assertEquals(MessageBus::class, $serviceDefinition->getClass());

    // The `sm.bus.default.middleware` parameter is set,
    // processed by registerBusMiddleware, then subsequently removed by
    // MessengerPass. Ensure it is not present, but the data should have been
    // mutated into the first parameter of the Bus service definition.
    static::assertFalse($this->container->hasParameter('sm.bus.default.middleware'));
    $arg1 = $serviceDefinition->getArgument(0);
    static::assertInstanceOf(IteratorArgument::class, $arg1);
    /** @var \Symfony\Component\DependencyInjection\Reference[] $arg1Array */
    $arg1Array = $arg1->getValues();
    static::assertCount(5, $arg1Array);
    $ids = array_map(fn (Reference $reference) => (string) $reference, $arg1Array);
    static::assertEquals([
      'sm.bus.default.middleware.add_bus_name_stamp_middleware',
      'sm.bus.default.middleware.dispatch_after_current_bus',
      'sm.bus.default.middleware.failed_message_processing_middleware',
      'sm.bus.default.middleware.send_message',
      'sm.bus.default.middleware.handle_message',
    ], $ids);

    // Ensure add_bus_name_stamp_middleware middleware for default has the
    // default service ID as its first arg.
    static::assertEquals('sm.bus.default', $this->container->getDefinition('sm.bus.default.middleware.add_bus_name_stamp_middleware')->getArgument(0));

    // The default bus.
    static::assertTrue($this->container->hasAlias('messenger.default_bus'));
    $aliasDefinition = $this->container->getAlias('messenger.default_bus');
    static::assertTrue($aliasDefinition->isPublic());
    static::assertEquals('sm.bus.default', (string) $aliasDefinition);
    $interfaceAliasDefinition = $this->container->getAlias(MessageBusInterface::class);
    static::assertTrue($interfaceAliasDefinition->isPublic());
    static::assertEquals('sm.bus.default', (string) $interfaceAliasDefinition);
  }

}
