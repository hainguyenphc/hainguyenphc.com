<?php

namespace Drupal\eg_sports\Controller;

use Drupal\Core\Controller\ControllerBase;

class TeamsController extends ControllerBase {

  protected static $table_teams = 'eg_sports_teams';

  protected static $table_players = 'eg_sports_players';

  public function getTeams() {
    $header = [
      'name' => 'Team',
      'description' => 'Description',
    ];

    $rows = [];

    $database = \Drupal::database();
    $result = $database
      ->select(TeamsController::$table_teams, 't')
      ->fields('t')
      ->execute();
    foreach ($result as $team) {
      $rows[] = [
        $team->name,
        $team->description,
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  public function getTitle() {
    return [
      '#markup' => 'Teams as of TODAY',
    ];
  }

}
