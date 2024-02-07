<?php

declare(strict_types = 1);

namespace Drupal\sm_test_transport;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Compiler pass.
 */
final class SmTestTransportCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $transports = $container->getParameter('sm_test_transport.transports');
    foreach ($transports as $name => $transport) {
      $this->createTransport($container, $name, array_merge($transport, [
        'dsn' => 'in-memory://?serialize=true',
        'options' => [],
      ]));
    }
  }

  /**
   * Create a transport definition.
   */
  private function createTransport(ContainerBuilder $container, string $name, array $transport): void {
    // Similar to FrameworkExtension.php:2192
    // Transports would normally be transformed from YAML to services.
    $serializerId = $transport['serializer'] ?? 'messenger.default_serializer';
    $transportDefinition = (new Definition(TransportInterface::class))
      ->setFactory([new Reference('messenger.transport_factory'), 'createTransport'])
      ->setArguments([
        $transport['dsn'],
        $transport['options'] + ['transport_name' => $name],
        new Reference($serializerId),
      ])
      ->addTag('messenger.receiver', [
        'alias' => $name,
        'is_failure_transport' => FALSE,
      ]);
    $container->setDefinition('sm_test_transport.transport.' . $name, $transportDefinition);
  }

}
