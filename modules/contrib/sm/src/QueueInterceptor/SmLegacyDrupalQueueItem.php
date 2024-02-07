<?php

declare(strict_types = 1);

namespace Drupal\sm\QueueInterceptor;

/**
 * @see \Drupal\sm\Messenger\SmLegacyDrupalQueueItemMessageHandler
 */
final class SmLegacyDrupalQueueItem {

  /**
   * Constructs a new LegacyDrupalQueueItem.
   */
  private function __construct(
    public mixed $data,
    public string $queueName,
  ) {
  }

  /**
   * Factory for creating a LegacyDrupalQueueItem.
   */
  public static function create(mixed $data, string $queueName): static {
    return new static($data, $queueName);
  }

}
