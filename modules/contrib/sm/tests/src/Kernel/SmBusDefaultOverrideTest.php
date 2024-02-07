<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Tests bus overrides.
 */
final class SmBusDefaultOverrideTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm',
  ];

  /**
   * Tests nullifying the default bus.
   */
  public function testDefaultBusRemoval(): void {
    $buses = $this->container->getParameter('sm.buses');
    static::assertArrayNotHasKey('default', $buses);
    static::assertArrayHasKey('foobar', $buses);

    // The bus named 'default'.
    static::assertFalse($this->container->hasDefinition('sm.bus.default'));
    static::assertFalse($this->container->hasParameter('sm.bus.default.middleware'));
    static::assertTrue($this->container->hasDefinition('sm.bus.foobar'));
    // This parameter is temporarily set. Ensure MessengerPass removed it.
    static::assertFalse($this->container->hasParameter('sm.bus.foobar.middleware'));

    // The default bus.
    $aliasDefinition = $this->container->getAlias('messenger.default_bus');
    static::assertTrue($aliasDefinition->isPublic());
    static::assertEquals('sm.bus.foobar', (string) $aliasDefinition);
    $interfaceAliasDefinition = $this->container->getAlias(MessageBusInterface::class);
    static::assertTrue($interfaceAliasDefinition->isPublic());
    static::assertEquals('sm.bus.foobar', (string) $interfaceAliasDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    parent::register($container);
    $container->setParameter('sm.default_bus', 'sm.bus.foobar');
    $container->setParameter('sm.buses', [
      'foobar' => [
        'middleware' => [],
        'default_middleware' => [
          'enabled' => TRUE,
        ],
      ],
    ]);
  }

}
