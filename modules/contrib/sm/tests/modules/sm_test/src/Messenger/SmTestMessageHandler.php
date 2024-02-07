<?php

declare(strict_types = 1);

namespace Drupal\sm_test\Messenger;

use Drupal\sm_test\SmTestMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * A message handler.
 */
#[AsMessageHandler]
final class SmTestMessageHandler {

  /**
   * Message handler for SmTestMessage messages.
   */
  public function __invoke(SmTestMessage $message): void {
    $message->handledBy = __METHOD__;
  }

}
