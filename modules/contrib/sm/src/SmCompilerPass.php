<?php

declare(strict_types = 1);

namespace Drupal\sm;

use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * SM compiler pass.
 */
final class SmCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $container->getDefinition('messenger.transport_factory')
      // Drupal cannot use tagged iterator in YAML so do it here:
      ->setArgument(0, new TaggedIteratorArgument('messenger.transport_factory'));

    // transports() must be before sendersLocator().
    $this->transports($container);
    $this->sendersLocator($container);

    // Buses.
    $defaultMiddleware = [
      'before' => [
        ['id' => 'add_bus_name_stamp_middleware'],
        ['id' => 'dispatch_after_current_bus'],
        ['id' => 'failed_message_processing_middleware'],
      ],
      'after' => [
        ['id' => 'send_message'],
        ['id' => 'handle_message'],
      ],
    ];

    // Similar to
    // symfony/framework-bundle/DependencyInjection/FrameworkExtension.php:2121.
    $defaultBus = $container->getParameter('sm.default_bus');
    /** @var array<string, array{middleware: array, default_middleware: array{enabled: bool}}> $buses */
    $buses = $container->getParameter('sm.buses');
    foreach ($buses as $id => $bus) {
      $busId = 'sm.bus.' . $id;

      // Middleware.
      $middleware = $bus['middleware'];
      if ($bus['default_middleware']['enabled']) {
        // Argument to add_bus_name_stamp_middleware.
        $defaultMiddleware['before'][0]['arguments'] = [$busId];

        $middleware = array_merge(
          $defaultMiddleware['before'],
          $middleware,
          $defaultMiddleware['after'],
        );
      }
      $container->setParameter($busId . '.middleware', $middleware);

      // Service registration.
      $container
        ->register($busId, MessageBus::class)
        ->addArgument([])
        ->addTag('messenger.bus');

      // When this is the default bus:
      if ($busId === $defaultBus) {
        // ...register it to special service and autowire alias.
        $container->setAlias('messenger.default_bus', $busId)->setPublic(TRUE);
        $container->setAlias(MessageBusInterface::class, $busId);
      }
    }
  }

  /**
   * Registers senders locator to the container.
   */
  private function sendersLocator(ContainerBuilder $container): void {
    $messageToSendersMapping = [];

    /** @var array<string|class-string, string|string[]> $messageToSendersMapping */
    $routing = $container->getParameter('sm.routing');
    foreach ($routing as $classOrWildcard => $transportSingleOrMultiple) {
      $messageToSendersMapping[$classOrWildcard] = is_string($transportSingleOrMultiple)
        ? [$transportSingleOrMultiple]
        : $transportSingleOrMultiple;
    }

    // Senders Service Locator:
    $senderReferences = [];
    $senderAliases = $container->findTaggedServiceIds('messenger.receiver');
    foreach ($senderAliases as $serviceId => $tags) {
      // Both service ID and service alias are added to the locator.
      // service_id => service_id.
      $senderReferences[$serviceId] = new Reference($serviceId);

      $aliases = array_column($tags, 'alias');
      $alias = reset($aliases);
      if ($alias === FALSE) {
        throw new \Exception(sprintf('Missing alias tag on receiver: %s', $serviceId));
      }

      // Alias => service_id.
      $senderReferences[$alias] = new Reference($serviceId);
    }

    $sendersServiceLocator = ServiceLocatorTagPass::register($container, $senderReferences);
    $container->getDefinition('messenger.senders_locator')
      // Similar to \Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension::registerMessengerConfiguration
      // mapping 'routing' to the service argument via $messageToSendersMapping.
      ->setArgument(0, $messageToSendersMapping)
      ->setArgument(1, $sendersServiceLocator);
  }

  /**
   * Registers custom transports so they may be collected by sendersLocator()
   */
  private function transports(ContainerBuilder $container): void {
    /** @var array<string, array<mixed>> $transports */
    $transports = $container->getParameter('sm.transports');

    // Similar to symfony/framework-bundle/DependencyInjection/
    // FrameworkExtension.php:2192
    // Transports would normally be transformed from YAML to services.
    foreach ($transports as $name => $transport) {
      $serializerId = $transport['serializer'] ?? 'messenger.default_serializer';
      $transportDefinition = (new Definition(TransportInterface::class))
        ->setFactory([new Reference('messenger.transport_factory'), 'createTransport'])
        ->setArguments([
          $transport['dsn'],
          ($transport['options'] ?? []) + ['transport_name' => $name],
          new Reference($serializerId),
        ])
        ->addTag('messenger.receiver', [
          'alias' => $name,
          'is_failure_transport' => FALSE,
        ]);
      $container->setDefinition('sm.transport.' . $name, $transportDefinition);
    }
  }

}
