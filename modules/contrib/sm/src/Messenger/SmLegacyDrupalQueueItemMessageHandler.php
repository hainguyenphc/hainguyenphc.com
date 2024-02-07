<?php

declare(strict_types = 1);

namespace Drupal\sm\Messenger;

use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\sm\QueueInterceptor\SmLegacyDrupalQueueItem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler for processing LegacyDrupalQueueItem messages.
 */
#[AsMessageHandler]
final class SmLegacyDrupalQueueItemMessageHandler {

  /**
   * Constructs a new LegacyDrupalQueueItemMessageHandler.
   */
  public function __construct(
    private readonly QueueWorkerManagerInterface $queueManager,
  ) {
  }

  /**
   * Processes a LegacyDrupalQueueItem message.
   */
  public function __invoke(SmLegacyDrupalQueueItem $message): void {
    $this->queueManager
      ->createInstance($message->queueName)
      ->processItem($message->data);
  }

}
