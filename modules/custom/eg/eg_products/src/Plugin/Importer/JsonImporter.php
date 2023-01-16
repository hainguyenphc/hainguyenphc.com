<?php

namespace Drupal\eg_products\Plugin\Importer;

use Drupal\Core\Batch\BatchBuilder;
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
    // Approach 1: brute force
    // foreach ($products as $product) {
    //   $this->persistProduct($product);
    // }
    // Approach 2: Batch API
    $bacth_builder = new BatchBuilder();
    $bacth_builder->setTitle(t('Importing products'));
    $bacth_builder->addOperation([$this, 'clearMissingProducts'], [$products]);
    $bacth_builder->addOperation([$this, 'doImportProducts'], [$products]);
    $bacth_builder->setFinishCallback([$this, 'importProductsFinishedCallback']);
    // After setting the batch, we actually need to trigger it via `batch_process()`.
    // But in this case, the form API will do that.
    // Remember, we submit the form to start importing products.
    // If we are using Drush to trigger the process, use `drush_backend_batch_process()`.
    batch_set($bacth_builder->toArray());

    return TRUE;
  }

  // Removes products that are currently in DB but are not in the new JSON source file.
  public function clearMissingProducts(array $products, array &$context) {
    if (!isset($context['results']['cleared'])) {
      $context['results']['cleared'] = [];
    }
    if (!$products) {
      return;
    }
    
    $ids = [];
    foreach ($products as $product) {
      $ids[] = $product->id;
    }
    $ids = $this->entity_type_manager->getStorage('product')
      ->getQuery()
      ->condition('remote_id', $ids, 'NOT IN')
      ->execute();
    if (!$ids) {
      $context['results']['cleared'] = [];
      return;
    }

    $entities = $this->entity_type_manager->getStorage('product')->loadMultiple($ids);
    foreach ($entities as $entity) {
      $context['results']['cleared'][] = $entity->getName();
    }

    $context['message'] = t('Removing @count products', [
      '@count' => count($entities),
    ]);

    $this->entity_type_manager->getStorage('product')->delete($entities);
  }

  // After clearing out obsolete products, we save the remainder.
  public function doImportProducts(array $products, array &$context) {
    if (!isset($context['results']['imported'])) {
      $context['results']['imported'] = [];
    }

    if (!$products) {
      return;
    }

    $sandbox = &$context['sandbox'];
    if (!$sandbox) {
      // A counter; value is [0, count($products)] range.
      $sandbox['progress'] = 0;
      $sandbox['max'] = count($products);
      $sandbox['products'] = $products;
    }

    $slice = array_splice($sandbox['products'], 0, 3);
    foreach ($slice as $product) {
      $context['message'] = t('Importing product @name', ['@name' => $product->name]);
      $this->persistProduct($product);
      $context['results']['imported'][] = $product->name;
      $sandbox['progress']++;
    }

    $context['finished'] = $sandbox['progress'] / $sandbox['max'];
  }

  /**
   * @param bool $success
   *    Indicates if the batch completes successfully.
   * @param array $results
   *    The $context['results'] arrays in `clearMissingProducts` and `doImportProducts`
   *    are merged together and passed in as `$results`.
   * @param array $operations
   *    A list of operations.
   */
  public function importProductsFinishedCallback($success, $results, $operations) {
    if (!$success) {
      // $this->messenger->addStatus(t('There was a problem with this batch.'), 'error');
      return;
    }

    $cleared = count($results['cleared']);
    if ($cleared === 0) {
      // $this->messenger->addStatus(t('No product was removed.'));
    }
    else {
      // $this->messenger->addStatus($this->formatPlural($cleared, '1 product was removed', '@count products were removed'));
    }

    $imported = count($results['imported']);
    if ($imported === 0) {
      // $this->messenger->addStatus(t('No product was imported.'));
    }
    else {
      // $this->messenger->addStatus($this->formatPlural($imported, '1 product was imported', '@count products were imported'));
    }
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
