<?php

declare(strict_types = 1);

namespace Drupal\sm_test\Messenger;

use Drupal\sm_test\SmTestServiceMessage;

/**
 * Message handler.
 */
final class SmTestMessageServiceHandler {

  /**
   * Message handler for SmTestServiceMessage messages.
   */
  public function __invoke(SmTestServiceMessage $message): void {
    $message->handledBy = __METHOD__;
  }

}
