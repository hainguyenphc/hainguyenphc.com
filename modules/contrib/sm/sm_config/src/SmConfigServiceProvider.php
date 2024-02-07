<?php

declare(strict_types = 1);

namespace Drupal\sm_config;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Service provider for SM Config.
 */
final class SmConfigServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container
      ->addCompilerPass(new SmConfigCompilerPass());
  }

}
