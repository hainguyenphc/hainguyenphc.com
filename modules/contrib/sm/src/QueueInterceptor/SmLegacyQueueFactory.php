<?php

declare(strict_types = 1);

namespace Drupal\sm\QueueInterceptor;

use Drupal\Core\Queue\QueueInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * A factory for queues which route through Messenger bus.
 */
final class SmLegacyQueueFactory {

  /**
   * Constructs a new LegacyQueueFactory.
   */
  public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
  }

  /**
   * This method must be named get.
   *
   * There is a soft requirement in
   * core/lib/Drupal/Core/Queue/QueueFactory.php:61 for this method name, even
   * though there is no interface.
   */
  public function get(string $queueName): QueueInterface {
    return SmLegacyQueue::createFromFactory($this->bus, $queueName);
  }

}
