<?php

namespace Drupal\Tests\eg_products\Kernel;

use Drupal\Core\File\FileSystemInterface;
use Drupal\KernelTests\KernelTestBase;

/** 
 * Test the CSV Product importer.
 * 
 * @group eg_products
 */
class CsvImporterTest extends KernelTestBase {

  /** 
   * Those modules are NOT installed, but loaded into the test environment.
   * 
   * {@inheritDoc}
   */
  protected static $modules = [
    'system',
    'csv_importer_test', // all configurations, source test files, etc.
    'eg_products',
    'image',
    'file',
    'user',
  ];

  public function testImportProducts() {
    // @see modules/custom/eg/eg_products/src/Entity/Product.php file.
    $this->installEntitySchema('product');
    $this->installEntitySchema('file');
    // The `file_usage` table from `file` module.
    $this->installSchema('file', 'file_usage');
    $entity_type_manager = $this->container->get('entity_type.manager');
    
    $products = $entity_type_manager->getStorage('product')->loadMultiple();
    $this->assertEmpty($products);

    $csv_path = \Drupal::service('extension.list.module')->getPath('csv_importer_test') . '/source.csv';
    $csv_contents = file_get_contents($csv_path);

    // Copy the CSV content to a temporary file for testing.
    // The file gets removed once testing is done as Drupal cleans after itself.
    $file = \Drupal::service('file.repository')->writeData(
      $csv_contents,
      'public://simpletest-products.csv',
      FileSystemInterface::EXISTS_REPLACE
    );

    $config = $entity_type_manager
      // @see modules/custom/eg/eg_products/src/Entity/Importer.php file.
      ->getStorage('importer')
      ->create([
        'id' => 'csv_123',
        'label' => 'CSV Importer',
        // @see modules/custom/eg/eg_products/src/Plugin/Importer/CsvImporter.php file.
        'plugin' => 'csv_importer',
        'plugin_configuration' => [
          'file' => [
            $file->id(),
          ],
          'source' => 'Testing',
          'bundle' => 'goods',
          'update_existing' => TRUE,
        ],
      ]);
    
    $config->save();

    // @see modules/custom/eg/eg_products/eg_products.services.yml file.
    // @see modules/custom/eg/eg_products/src/Plugin/ImporterPluginManager.php file.
    $plugin = $this->container->get('products.importer_manager')->createInstanceFromConfig('csv_123');
    $plugin->importProducts();
    $products = $entity_type_manager->getStorage('product')->loadMultiple();
    $this->assertCount(2, $products);

    $products = $entity_type_manager
      ->getStorage('product')
      // @see modules/custom/eg/eg_products/tests/modules/csv_importer_test/source.csv file.
      ->loadByProperties([
        'number' => '45345',
      ]);
    $this->assertNotEmpty($products);
    $this->assertCount(1, $products);
  }

}
