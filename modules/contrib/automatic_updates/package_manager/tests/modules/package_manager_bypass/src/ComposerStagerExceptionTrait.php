<?php

declare(strict_types = 1);

namespace Drupal\package_manager_bypass;

/**
 * Trait to make Composer Stager throw pre-determined exceptions in tests.
 *
 * @internal
 */
trait ComposerStagerExceptionTrait {

  /**
   * Sets an exception to be thrown.
   *
   * @param \Throwable|null $exception
   *   The exception to throw, or NULL to delete a stored exception.
   */
  public static function setException(?\Throwable $exception): void {
    \Drupal::state()->set(static::class . '-exception', $exception);
  }

  /**
   * Throws the exception if set.
   */
  private function throwExceptionIfSet(): void {
    if ($exception = $this->state->get(static::class . '-exception')) {
      throw $exception;
    }
  }

}
