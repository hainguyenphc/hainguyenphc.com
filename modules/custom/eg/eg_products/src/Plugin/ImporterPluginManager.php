<?php

namespace Drupal\eg_products\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use GuzzleHttp\RetryMiddleware;

/**
 * Provides the Importer plugin manager.
 *
 * All plugin managers are service.
 * @see eg_products.services.yml file.
 */
class ImporterPluginManager extends DefaultPluginManager {

  protected $entity_type_manager;

  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    // The namespace where plugins of Importer type could be found.
    $importer_plugin_namespace = 'Plugin/Importer';
    // The interface all Importer plugins must impl.
    $importer_interface = 'Drupal\eg_products\Plugin\ImporterPluginInterface';
    // Custom annotation class to denote all Importer plugins.
    $importer_annotation = 'Drupal\eg_products\Annotation\Importer';

    parent::__construct(
      $importer_plugin_namespace,
      $namespaces,
      $module_handler,
      $importer_interface,
      $importer_annotation
    );

    $this->entity_type_manager = $entity_type_manager;

    /**
     * Other modules could use hook_products_importer_info_alter() to change the
     * found/discovered plugin definitions.
     */
    $this->alterInfo('products_importer_info');

    /**
     * Specifies the cache key to cache the discovered plugin definitions.
     */
    $this->setCacheBackend($cache_backend, 'products_importer_plugins');
  }

  /**
   * @return \Drupal\eg_products\Entity\ImporterInterface
   */
  public function createInstanceFromConfig($importer_id) {
    $config = $this->entity_type_manager
      ->getStorage('importer')
      ->load($importer_id);

    if (!($config instanceof \Drupal\eg_products\Entity\ImporterInterface)) {
      return NULL;
    }

    /** @var \Drupal\eg_products\Entity\ImporterInterface */
    $importer = $this->createInstance(
      $config->getPluginId(),
      ['config' => $config]
    );

    return $importer;
  }

  /**
   * Discovers all Importer config entities and invokes them.
   *
   * @return array
   */
  public function createInstanceFormAllConfigs() {
    $importer_config_entities = $this->entity_type_manager
      ->getStorage('importer')
      ->loadMultiple();

    if (!$importer_config_entities) {
      return [];
    }

    $plugins = [];

    foreach ($importer_config_entities as $importer) {
      $importer_id = $importer->id();
      $plugin = $this->createInstanceFromConfig($importer_id);
      if (!$plugin) {
        continue;
      }
      else {
        $plugins[] = $plugin;
      }
    }

    return $plugins;
  }

}
