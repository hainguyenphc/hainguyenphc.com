<?php

declare(strict_types = 1);

namespace Drupal\sm;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Service provider for unprivatizing transports.
 *
 * Workaround for https://www.drupal.org/project/drupal/issues/3391860
 */
final class SmUnprivatizeTransportsCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $receivers = $container->findTaggedServiceIds('messenger.receiver');
    foreach (array_keys($receivers) as $serviceId) {
      $container
        ->getDefinition($serviceId)
        ->setPublic(TRUE);
    }
  }

}
