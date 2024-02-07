<?php

declare(strict_types = 1);

namespace Drupal\sm_test\Messenger;

use Drupal\sm_test\SmTestClassMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Message handler class-tagged with #[AsMessageHandler].
 */
#[AsMessageHandler]
final class SmTestMessageClassHandler {

  /**
   * Message handler for TestClassMessage messages.
   */
  public function __invoke(SmTestClassMessage $message): void {
    $message->handledBy = __METHOD__;
  }

}
