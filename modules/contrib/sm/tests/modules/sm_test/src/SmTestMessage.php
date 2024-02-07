<?php

declare(strict_types = 1);

namespace Drupal\sm_test;

/**
 * Use this message when not concerned about the behaviour of the handler.
 *
 * @see \Drupal\sm_test\Messenger\SmTestMessageHandler
 */
final class SmTestMessage {

  /**
   * Creates a new SmTestMessage.
   */
  public function __construct(
    public ?string $handledBy = NULL,
  ) {
  }

}
