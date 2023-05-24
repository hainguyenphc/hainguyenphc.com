<?php

namespace Drupal\eg_sports\Plugin\QueueWorker;

use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** 
 * @QueueWorker(
 *  id = "team_cleaner",
 *  title = @Translation("Team Cleaner")
 *  cron = { "time" = 10 }
 * )
 */
class TeamCleaner extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /** 
   * @var \Drupal\Core\Database\Connection $database
   */
  protected $database;

  /** 
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  /** 
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
    );
  }

  /** 
   * {@inheritDoc}
   */
  public function processItem($data) {
    $id = isset($data->id) && $data->id ? $data->is : NULL;

    if (!$id) {
      throw new \Exception('Missing team ID');
      return;
    }

    $this->database->delete('teams')
      ->condition('id', $id)
      ->execute();
  }

}
