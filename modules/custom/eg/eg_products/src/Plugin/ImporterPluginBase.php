<?php

namespace Drupal\eg_products\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\eg_products\Entity\ImporterInterface;
use Drupal\eg_products\Plugin\ImporterPluginInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ImporterPluginBase
  extends PluginBase
  implements ImporterPluginInterface, ContainerFactoryPluginInterface {

  // /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  // protected $entity_type_manager;

  // /** @var \GuzzleHttp\Client */
  // protected $guzzle_http_client;

  // public function __construct(
  //   array $configuration,
  //   $plugin_id, $plugin_definition,
  //   EntityTypeManager $entity_type_manager,
  //   Client $guzzle_http_client
  // ) {
  //   parent::__construct($configuration, $plugin_id, $plugin_definition);

  //   $this->entity_type_manager = $entity_type_manager;
  //   $this->guzzle_http_client = $guzzle_http_client;

  //   if (!isset($configuration['config'])) {
  //     throw new PluginException('Missing Importer configuration.');
  //   }

  //   // if (!($configuration['config'] instanceof ImporterPluginInterface)) {
  //   if (!($configuration['config'] instanceof ImporterInterface)) {
  //     throw new PluginException('Incorrect Importer configuration');
  //   }
  // }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static (
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig() {
    return $this->configuration['config'];
  }

}
