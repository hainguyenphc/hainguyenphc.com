<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Kernel;

use Drupal\Core\State\StateInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\sm\QueueInterceptor\SmLegacyQueueFactory;
use Drupal\sm_test\Plugin\QueueWorker\SmTestQueueWorker;

/**
 * Tests queue interceptor.
 *
 * @covers \Drupal\sm\Messenger\SmLegacyDrupalQueueItemMessageHandler
 * @covers \Drupal\sm\QueueInterceptor\SmLegacyDrupalQueueItem
 * @covers \Drupal\sm\QueueInterceptor\SmLegacyQueue
 * @covers \Drupal\sm\QueueInterceptor\SmLegacyQueueFactory
 */
final class SmQueueInterceptorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm_test',
    'sm',
  ];

  /**
   * Tests legacy queue.
   *
   * Since we are using messenger without a transport, it will be executed
   * immediately. Whereas Drupal core queues or messenger with configured
   * transport needs to be run explicitly.
   */
  public function testQueueIntercept(): void {
    $this->setSetting('queue_default', SmLegacyQueueFactory::class);
    $data = [$this->randomString()];
    \Drupal::queue(SmTestQueueWorker::PLUGIN_ID)->createItem($data);
    static::assertEquals($data, $this->state()->get(SmTestQueueWorker::class));
  }

  /**
   * State.
   */
  private function state(): StateInterface {
    return \Drupal::service('state');
  }

}
