<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sm\Command\SmConsumeMessagesCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests consume command.
 */
final class SmConsumeCommandTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sm',
    'sm_test_transport',
  ];

  /**
   * Tests command can run.
   */
  public function testCommand(): void {
    $tester = new CommandTester($this->command());
    $result = $tester->execute([
      'receivers' => ['sm_test_transport__in_memory'],
      '--time-limit' => 5,
    ]);

    static::assertEquals(0, $result);
    $display = $tester->getDisplay();
    static::assertStringContainsString('[OK] Consuming messages from transport "sm_test_transport__in_memory".', $display);
    static::assertStringContainsString('The worker will automatically exit once it has been running for 5s', $display);
  }

  /**
   * Command.
   */
  private function command(): SmConsumeMessagesCommand {
    return \Drupal::service('console.command.messenger_consume_messages');
  }

}
