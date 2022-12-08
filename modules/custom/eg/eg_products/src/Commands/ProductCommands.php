<?php

namespace Drupal\eg_products\Commands;

use Drupal\eg_products\Plugin\ImporterPluginInterface;
use Drupal\eg_products\Plugin\ImporterPluginManager;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Input\InputOption;

class ProductCommands extends DrushCommands {

  /**
   * @var Drupal\eg_products\Plugin\ImporterPluginManager
   */
  protected $importer_manager;

  public function __construct(ImporterPluginManager $importer_manager) {
    $this->importer_manager = $importer_manager;
  }

  /**
   * Discovers all Importer config entities and invokes them.
   *
   * @option importer
   *  The importer config ID
   * @command products-import-run
   * @aliases pir
   *
   * @param array $options
   *
   * drush products-import-run
   * drush products-import-run -importer=json_goods_importer
   *
   */
  public function import(
    $options = ['importer' => InputOption::VALUE_OPTIONAL]
  ) {
    $importer_id = $options['importer'];
    if (!is_null($importer_id)) {
      $plugin = $this->importer_manager->createInstanceFromConfig($importer_id);
      if (is_null($plugin)) {
        $this->logger()->log(
          'error',
          t(
            'The %importer importer does not exist.',
            ['%importer' => $importer_id]
          )
        );
        return;
      }
      $this->importWithPlugin($plugin);
      return;
    }

    $plugins = $this->importer_manager->createInstanceFormAllConfigs();
    if (!$plugins) {
      $this->logger()->log(
        'error',
        t(
          'There is no importer config entity types to run.'
        )
      );
      return;
    }
    foreach ($plugins as $plugin) {
      $this->importWithPlugin($plugin);
    }
  }

  protected function importWithPlugin(ImporterPluginInterface $plugin) {
    $success = $plugin->importProducts();
    $message_values = ['@importer' => $plugin->getConfig()->label()];
    if ($success) {
      $this->logger()->log(
        'status',
        t('The "@importer" importer has run successfully.', $message_values)
      );
      return;
    }
    $this->logger()->log(
      'error',
      t('The "@importer" importer has run failed.', $message_values)
    );
  }

}
