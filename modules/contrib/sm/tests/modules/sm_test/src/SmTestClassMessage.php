<?php

declare(strict_types = 1);

namespace Drupal\sm_test;

/**
 * @see \Drupal\sm_test\Messenger\SmTestMessageClassHandler
 */
final class SmTestClassMessage {

  /**
   * Creates a new TestClassMessage.
   */
  public function __construct(
    public ?string $handledBy = NULL,
  ) {
  }

}
