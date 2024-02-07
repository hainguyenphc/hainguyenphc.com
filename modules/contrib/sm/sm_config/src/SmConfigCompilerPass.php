<?php

declare(strict_types = 1);

namespace Drupal\sm_config;

use Drupal\Core\Config\BootstrapConfigStorageFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * SM Config compiler pass.
 */
final class SmConfigCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    // Senders Message Mapping:
    // Get any predefined routing from the service.yml files, etc. This
    // effectively becomes a merge strategy, except if a message is configured
    // here, it will override previously defined routing.
    $messageToSendersMapping = $container
      ->getDefinition('messenger.senders_locator')
      ->getArgument(0);
    foreach ($this->getSettings()['routing'] as ['message' => $message, 'receivers' => $receivers]) {
      $messageToSendersMapping[$message] = $receivers;
    }

    $container->getDefinition('messenger.senders_locator')
      ->replaceArgument(0, $messageToSendersMapping);

    // Bus and Message x Bus Map for internal use:
    // The module needs the full list of Buses, but there is no canonical way of
    // getting them with services. So we do the same as Symfony's data collector
    // method in
    // \Symfony\Component\Messenger\DependencyInjection\MessengerPass::process
    // by recording to a parameter privately.
    // Also try to determine which messages we have handlers for by reverse
    // engineering how 'console.command.messenger_debug' is constructed in the
    // same MessengerPass.
    $messageBusMap = [];
    foreach ($container->findTaggedServiceIds('messenger.bus') as $busId => $tags) {
      $locatorArg0 = $container
        ->getDefinition($busId . '.messenger.handlers_locator')
        ->getArgument(0);
      $messageBusMap[$busId] = array_keys($locatorArg0);
    }
    $container->setParameter('sm_config.message_bus_map', $messageBusMap);
  }

  /**
   * Get active configuration.
   *
   * @return array{'routing': array{'bus': string, 'message': string|class-string, 'receiver': string}}
   *   The active configuration.
   */
  private function getSettings(): array {
    $settings = BootstrapConfigStorageFactory::get()->read('sm_config.settings');
    return ($settings !== FALSE ? $settings : []) + [
      'routing' => [],
    ];
  }

}
