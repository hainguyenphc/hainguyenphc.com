<?php

declare(strict_types = 1);

namespace Drupal\sm_test\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *   id = \Drupal\sm_test\Plugin\QueueWorker\SmTestQueueWorker::PLUGIN_ID,
 *   title = @Translation("Queue test"),
 *   cron = {"time" = 60}
 * )
 */
final class SmTestQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  public const PLUGIN_ID = 'sm_test_test_queue';

  private StateInterface $state;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->state = $container->get('state');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem(mixed $data): void {
    $this->state->set(static::class, $data);
  }

}
