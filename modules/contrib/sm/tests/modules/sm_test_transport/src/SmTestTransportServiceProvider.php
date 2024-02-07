<?php

declare(strict_types = 1);

namespace Drupal\sm_test_transport;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Service provider.
 */
final class SmTestTransportServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container
      // Run before 'Senders Service Locator' is built in
      // SmCompilerPass (priority: 0).
      ->addCompilerPass(new SmTestTransportCompilerPass(), priority: 100);
  }

}
