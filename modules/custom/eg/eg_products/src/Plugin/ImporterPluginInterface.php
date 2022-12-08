<?php

namespace Drupal\eg_products\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface ImporterPluginInterface extends PluginInspectionInterface {

  /**
   * Performs importing products. Returns TRUE if operation was successful and
   * FALSE otherwise.
   *
   * @return bool
   */
  public function importProducts();

  /**
   * @return \Drupal\eg_products\Entity\ImporterInterface
   */
  public function getConfig();

}
