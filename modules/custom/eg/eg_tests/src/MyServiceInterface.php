<?php

namespace Drupal\eg_tests;

interface MyServiceInterface {
  /**
   * Gets the label.
   * @param string $key
   *    The machine name for the label/message pair.
   * @return string|bool
   *    The human readable label if found; FALSE otherwise.
   */
  public function getLabel($key);

  /**
   * Gets the message.
   * @param string $key
   *    The machine name for the label/message pair.
   * @return string|bool
   *    The human readable label if found; FALSE otherwise.
   */
  public function getMessage($key);
}
