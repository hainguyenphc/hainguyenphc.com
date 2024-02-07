<?php

declare(strict_types = 1);

namespace Drupal\sm_test;

/**
 * @see \Drupal\sm_test\Messenger\SmTestMessageMethodHandler
 */
final class SmTestMethod2Message {

  /**
   * Creates a new TestMethod2Message.
   */
  public function __construct(
    public ?string $handledBy = NULL,
  ) {
  }

}
