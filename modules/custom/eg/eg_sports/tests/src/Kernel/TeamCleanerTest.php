<?php

namespace Drupal\Tests\eg_sports\Kernel;

use Drupal\eg_sports\Plugin\QueueWorker\TeamCleaner;
use Drupal\KernelTests\KernelTestBase;

/** 
 * Test the TeamCleaner QueueWorker plugin.
 * 
 * @group eg_sports
 */
class TeamCleanerTest extends KernelTestBase {

  /** 
   * The modules listed here are not actually installed.
   * They are loaded and added to the service container only.
   * That means the schemas/tables in hook_install do not get created.
   * 
   * {@inheritDoc}
   */
  protected static $modules = ['eg_sports'];

  /** 
   * Test Drupal\eg_sports\Plugin\QueueWorker\TeamCleaner::processItem() method.
   */
  public function testProcessItem() {
    // Install the `eg_sports_teams` table only.
    // Remember: we also have `eg_sports_players` table.
    $this->installSchema('eg_sports', 'eg_sports_teams');

    // Access the database service from container.
    $database = $this->container->get('database');
    
    // Insert a single row into the `eg_sports_players` table.
    // No description and location, just bare minimum.
    $fields = ['name' => 'Real Madrid'];
    $id = $database->insert('eg_sports_teams')->fields($fields)->execute();
    $records = $database->query("SELECT id FROM {eg_sports_teams} WHERE id = :id", [':id' => $id])->fetchAll();
    $this->assertNotEmpty($records);
    
    // Test if the worker actually removes the row.
    // Empty configuration, no plugin id, no plugin definition.
    $worker = new TeamCleaner([], NULL, NULL, $database);
    $data = new \stdClass();
    $data->id = $id;
    $worker->processItem($data);
    $records = $database->query("SELECT id FROM {eg_sports_teams} WHERE id = :id", [':id' => $id])->fetchAll();
    $this->assertEmpty($records);
  }

}
