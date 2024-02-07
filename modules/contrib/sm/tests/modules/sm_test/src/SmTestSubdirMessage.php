<?php

declare(strict_types = 1);

namespace Drupal\sm_test;

/**
 * @see \Drupal\sm_test\Messenger\Subdir\SmTestMessageSubdirHandler
 */
final class SmTestSubdirMessage {

  /**
   * Creates a new TestSubdirMessage.
   */
  public function __construct(
    public ?string $handledBy = NULL,
  ) {
  }

}
