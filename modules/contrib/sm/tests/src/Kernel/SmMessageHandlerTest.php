<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sm_test\SmTestClassMessage;
use Drupal\sm_test\SmTestMethod2Message;
use Drupal\sm_test\SmTestMethodMessage;
use Drupal\sm_test\SmTestServiceMessage;
use Drupal\sm_test\SmTestSubdirMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Tests message handlers.
 */
final class SmMessageHandlerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm_test',
    'sm',
  ];

  /**
   * Tests #[AsMessageHandler] on class.
   *
   * @see \Drupal\sm_test\Messenger\SmTestMessageClassHandler
   */
  public function testAttributeOnClass(): void {
    $message = new SmTestClassMessage();
    $this->bus()->dispatch($message);
    static::assertEquals('Drupal\sm_test\Messenger\SmTestMessageClassHandler::__invoke', $message->handledBy);
  }

  /**
   * Tests #[AsMessageHandler] on class in a subdirectory of Messenger/.
   *
   * @see \Drupal\sm_test\Messenger\Subdir\SmTestMessageSubdirHandler
   */
  public function testAttributeOnClassInSubdir(): void {
    $message = new SmTestSubdirMessage();
    $this->bus()->dispatch($message);
    static::assertEquals('Drupal\sm_test\Messenger\Subdir\SmTestMessageSubdirHandler::__invoke', $message->handledBy);
  }

  /**
   * Tests #[AsMessageHandler] on methods.
   *
   * @see \Drupal\sm_test\Messenger\SmTestMessageMethodHandler::fooBar()
   * @see \Drupal\sm_test\Messenger\SmTestMessageMethodHandler::fooBar2()
   */
  public function testAttributeOnMethod(): void {
    $message = new SmTestMethodMessage();
    $this->bus()->dispatch($message);
    static::assertEquals('Drupal\sm_test\Messenger\SmTestMessageMethodHandler::fooBar', $message->handledBy);

    $message = new SmTestMethod2Message();
    $this->bus()->dispatch($message);
    static::assertEquals('Drupal\sm_test\Messenger\SmTestMessageMethodHandler::fooBar2', $message->handledBy);
  }

  /**
   * Tests message handler defined as a service.
   *
   * Without attribute and outside of Messenger/ dir.
   *
   * @see \Drupal\sm_test\SmTestMessageServiceHandler
   */
  public function testService(): void {
    $message = new SmTestServiceMessage();
    $this->bus()->dispatch($message);
    static::assertEquals('Drupal\sm_test\Messenger\SmTestMessageServiceHandler::__invoke', $message->handledBy);
  }

  /**
   * Default messenger bus.
   */
  private function bus(): MessageBusInterface {
    return \Drupal::service('messenger.default_bus');
  }

}
