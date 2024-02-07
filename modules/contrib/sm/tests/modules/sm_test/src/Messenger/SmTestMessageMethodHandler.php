<?php

declare(strict_types = 1);

namespace Drupal\sm_test\Messenger;

use Drupal\sm_test\SmTestMethod2Message;
use Drupal\sm_test\SmTestMethodMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Message handler method-tagged with #[AsMessageHandler].
 */
final class SmTestMessageMethodHandler {

  /**
   * Message handler for TestMethodMessage messages.
   */
  #[AsMessageHandler]
  public function fooBar(SmTestMethodMessage $message): void {
    $message->handledBy = __METHOD__;
  }

  /**
   * Message handler for TestMethod2Message messages.
   */
  #[AsMessageHandler]
  public function fooBar2(SmTestMethod2Message $message): void {
    $message->handledBy = __METHOD__;
  }

}
