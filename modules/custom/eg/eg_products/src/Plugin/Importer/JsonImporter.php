<?php

namespace Drupal\eg_products\Plugin\Importer;

use Drupal\eg_products\Plugin\ImporterPluginBase;

/**
 * @Importer(
 *  id = "json_importer",
 *  label = @Translation("JSON Importer")
 * )
 */
class JsonImporter extends ImporterPluginBase {

  public function importProducts() {
    $data = $this->getData();

    if (!$data) {
      return FALSE;
    }

    if (!isset($data->products)) {
      return FALSE;
    }

    $products = $data->products;
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
  private function getData() {
    /** @var \Drupal\eg_products\Entity\ImporterInterface */
    $config = $this->configuration['config'];
    /** @var string */
    $uri = $config->getSourceUrl()->toString();
    $request = $this->guzzle_http_client->get($uri);
    $string = $request->getBody()->getContents();
    return json_decode($string);
  }

  /**
   * Saves a Product entity into database and updates it if necessary.
   *
   * @param \stdClass $product
   */
  private function persistProduct($product) {
    /** @var \Drupal\eg_products\Entity\ImporterInterface */
    $config = $this->configuration['config'];

    $existing_product = $this->entity_type_manager->getStorage('product')
      ->loadByProperties([
        'remote_id' => $product->id,
        'source' => $config->getSource(),
      ]
    );

    if(!$existing_product) {
      $values = [
        'remote_id' => $product->id,
        'source' => $config->getSource(),
        // product type (bundle)
        'type' => $config->getBundle(),
      ];
      /** @var \Drupal\eg_products\Entity\ProductInterface */
      $new_product = $this->entity_type_manager
        ->getStorage('product')
        ->create($values);

      $new_product->setName($product->name);
      $new_product->setProductNumber($product->number);
      $new_product->save();

      return;
    }

    if (!$config->updateExistingProducts()) {
      return;
    }
    /** @var \Drupal\eg_products\Entity\ProductInterface */
    $existing_product = reset($existing_product);
    $existing_product->setName($product->name);
    $existing_product->setProductNumber($product->number);
    $existing_product->save();
  }

}
