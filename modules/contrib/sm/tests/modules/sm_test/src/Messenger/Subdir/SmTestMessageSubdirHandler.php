<?php

declare(strict_types = 1);

namespace Drupal\sm_test\Messenger\Subdir;

use Drupal\sm_test\SmTestSubdirMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Message handler in a subdirectory.
 */
#[AsMessageHandler]
final class SmTestMessageSubdirHandler {

  /**
   * Message handler for TestSubdirMessage messages.
   */
  public function __invoke(SmTestSubdirMessage $message): void {
    $message->handledBy = __METHOD__;
  }

}
