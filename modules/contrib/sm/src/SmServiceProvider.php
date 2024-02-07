<?php

declare(strict_types = 1);

namespace Drupal\sm;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

/**
 * Service provider for SM.
 */
final class SmServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container
      // Priority 200 to execute before AttributeAutoconfigurationPass which has
      // priority 100. See symfony/dependency-injection/Compiler/PassConfig.php.
      ->addCompilerPass(new SmMessageHandlerCompilerPass(), priority: 200)
      ->addCompilerPass(new SmCompilerPass())
      ->addCompilerPass(new MessengerPass())
      ->addCompilerPass(new SmUnprivatizeTransportsCompilerPass());
  }

}
