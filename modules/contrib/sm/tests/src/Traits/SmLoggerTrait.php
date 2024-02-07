<?php

declare(strict_types = 1);

namespace Drupal\Tests\sm\Traits;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Symfony\Component\ErrorHandler\BufferingLogger;

/**
 * Logger trait.
 */
trait SmLoggerTrait {

  /**
   * Gets logs from buffer and cleans out buffer.
   *
   * Reconstructs logs into plain strings.
   *
   * @param array|null $logBuffer
   *   A log buffer from getLogBuffer, or provide an existing value fetched from
   *   getLogBuffer. This is a workaround for the logger clearing values on
   *   call.
   *
   * @return array<array{severity: \Drupal\Core\Logger\RfcLogLevel::*, message: string}>
   *   Logs from buffer, where values are an array with keys: severity, message.
   */
  private function getLogs(?array $logBuffer = NULL): array {
    $logs = array_map(static function (array $log): array {
      [$severity, $message, $context] = $log;
      return [
        'severity' => $severity,
        'message' => str_replace(array_keys($context), array_values($context), $message),
      ];
    }, $logBuffer ?? $this->getLogBuffer());
    return array_values($logs);
  }

  /**
   * Gets logs from buffer and cleans out buffer.
   *
   * @array
   *   Logs from buffer, where values are an array with keys: severity, message.
   */
  private function getLogBuffer(): array {
    return $this->container->get('.test_logger')->cleanLogs();
  }

  /**
   * Registers logger to container.
   */
  public function loggerRegister(ContainerBuilder $container): void {
    $container
      ->register('.test_logger', BufferingLogger::class)
      ->addTag('logger');
  }

  /**
   * Clears out logs on teardown.
   *
   * Must be called otherwise logs are sent to stderr.
   */
  protected function loggerTeardown(): void {
    $this->container->get('.test_logger')->cleanLogs();
  }

}
