<?php

namespace Drupal\eg_products\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Url;

interface ImporterInterface extends ConfigEntityInterface {

  /**
   * @return Url
   */
  public function getSourceUrl();

  /**
   * @return string
   */
  public function getPluginId();

  /**
   * Returns TRUE if importing also updates existing products; FALSE, otherwise.
   *
   * @return bool
   */
  public function updateExistingProducts();

  /**
   * Returns the source of products.
   *
   * @return string
   */
  public function getSource();

  /**
   * Returns the Product type (bundle) that is created.
   *
   * @return string
   */
  public function getBundle();

}
