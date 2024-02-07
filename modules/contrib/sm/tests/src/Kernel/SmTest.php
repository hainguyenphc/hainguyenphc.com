<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\sm_test\Messenger\SmTestMessageServiceHandler;
use Drupal\sm_test\SmTestServiceMessage;
use Drupal\Tests\sm\Traits\SmLoggerTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * Tests SM.
 */
final class SmTest extends KernelTestBase {

  use SmLoggerTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm',
    'sm_test',
  ];

  /**
   * Tests the basic operation of sending a message.
   */
  public function testMessageHandler(): void {
    $message = new SmTestServiceMessage();
    $envelope = $this->bus()->dispatch($message);
    // Since we have no transports, it is handled immediately:
    static::assertCount(2, $envelope->all());
    static::assertEquals('sm.bus.default', $envelope->last(BusNameStamp::class)->getBusName());
    static::assertEquals(SmTestMessageServiceHandler::class . '::__invoke', $envelope->last(HandledStamp::class)->getHandlerName());
    static::assertNull($envelope->last(HandledStamp::class)->getResult());
    static::assertEquals(SmTestMessageServiceHandler::class . '::__invoke', $message->handledBy);

    $logs = $this->getLogs();
    static::assertCount(1, $logs);
    static::assertEquals('Message {Drupal\sm_test\SmTestServiceMessage} handled by {Drupal\sm_test\Messenger\SmTestMessageServiceHandler::__invoke}', $logs[0]['message']);
  }

  /**
   * Default messenger bus.
   */
  private function bus(): MessageBusInterface {
    return \Drupal::service(MessageBusInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    parent::register($container);
    $this->loggerRegister($container);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    $this->loggerTeardown();
    parent::tearDown();
  }

}
