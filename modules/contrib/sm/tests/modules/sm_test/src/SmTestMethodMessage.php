<?php

declare(strict_types = 1);

namespace Drupal\sm_test;

/**
 * @see \Drupal\sm_test\Messenger\SmTestMessageMethodHandler
 */
final class SmTestMethodMessage {

  /**
   * Creates a new TestMethodMessage.
   */
  public function __construct(
    public ?string $handledBy = NULL,
  ) {
  }

}
