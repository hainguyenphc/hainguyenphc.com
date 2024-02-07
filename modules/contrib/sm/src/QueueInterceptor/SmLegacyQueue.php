<?php

declare(strict_types = 1);

namespace Drupal\sm\QueueInterceptor;

use Drupal\Core\Queue\QueueInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Captures queue messages and re-routes them to a messenger bus instead.
 */
final class SmLegacyQueue implements QueueInterface {

  /**
   * Constructs a new LegacyQueue.
   */
  private function __construct(
    private readonly MessageBusInterface $bus,
    private string $queueName,
  ) {
  }

  /**
   * Internal instance creation method.
   *
   * This method is internal and must only be called from
   * \Drupal\sm\QueueInterceptor\LegacyQueueFactory::get.
   *
   * @internal
   * @see \Drupal\sm\QueueInterceptor\SmLegacyQueueFactory::get
   */
  public static function createFromFactory(
    MessageBusInterface $bus,
    string $queueName,
  ): static {
    return new static($bus, $queueName);
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param mixed $data
   */
  public function createItem($data): bool|int|string {
    $message = SmLegacyDrupalQueueItem::create(data: $data, queueName: $this->queueName);
    $this->bus->dispatch($message);
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function numberOfItems(): int {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function claimItem($lease_time = 3600): object|false {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param mixed $item
   */
  public function deleteItem($item): void {
    throw new \LogicException('There is never anything in this queue so nothing can be deleted.');
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param mixed $item
   */
  public function releaseItem($item): bool {
    throw new \LogicException('There is never anything in this queue so nothing can be deleted.');
  }

  /**
   * {@inheritdoc}
   */
  public function createQueue(): void {
    // No-op.
  }

  /**
   * {@inheritdoc}
   */
  public function deleteQueue(): void {
    // No-op.
  }

}
