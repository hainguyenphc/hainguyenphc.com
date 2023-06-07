<?php

namespace Drupal\eg_products\Plugin\Importer;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\eg_products\Plugin\ImporterPluginBase;

/** 
 * @Importer(
 *  id = "csv_importer",
 *  label = @Translation("CSV Importer")
 * )
 */
class CsvImporter extends ImporterPluginBase {

  use StringTranslationTrait;

  /** 
   * {@inheritDoc}
   */
  public function importProducts() {
    $products = $this->getData();

    if (!$products) {
      return FALSE;
    }

    foreach ($products as $product) {
      $this->persistProduct($product);
    }

    return TRUE;
  }

  /**
   * Loads the product data from the remote source URL.
   *
   * @return \stdClass
   */
  private function getData() {}

  /**
   * Saves a Product entity into database and updates it if necessary.
   *
   * @param \stdClass $product
   */
  private function persistProduct($product) {}

}
