<?php

namespace Drupal\eg_sports\Controller;

use Drupal\Core\Controller\ControllerBase;

class PlayersController extends ControllerBase {

  public function getPlayers() {
    $database = \Drupal::database();

    // $query = "SELECT
    //     p.[name] as player_name,
    //     p.[data] as player_data,
    //     t.[name] as team_name
    //   FROM {eg_sports_players} p
    //   JOIN {eg_sports_teams} t
    //   ON t.[id] = p.[team_id]
    //   WHERE p.[id] > 0";
    // $result = $database->query($query)->fetchAll();

    $query = $database->select('eg_sports_players', 'p');
    $query->join('eg_sports_teams', 't', 'p.team_id = t.id');
    $query->addField('p', 'id', 'player_id');
    $query->addField('p', 'name', 'player_name');
    $query->addField('p', 'data', 'player_data');
    $query->addField('t', 'name', 'team_name');
    $result = $query
      ->condition('p.id', 0, '>')
      ->execute()
      ->fetchAll();

    $header = [
      'player' => 'Player',
      'country' => 'Country',
      'known_for' => 'Summary',
      'team' => 'Team',
    ];

    $rows = [];

    foreach($result as $record) {
      $data = unserialize($record->player_data);
      $country = $data['country'];
      $known_for = $data['known_for'];

      $rows[] = [
        $record->player_name,
        $country,
        $known_for,
        $record->team_name,
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  public function getPlayersInPager() {
    $database = \Drupal::database();

    $query = $database->select('eg_sports_players', 'p');
    $query->join('eg_sports_teams', 't', 'p.team_id = t.id');
    $query->addField('p', 'id', 'player_id');
    $query->addField('p', 'name', 'player_name');
    $query->addField('p', 'data', 'player_data');
    $query->addField('t', 'name', 'team_name');
    $result = $query
      ->condition('p.id', 0, '>')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(1)
      ->execute()
      ->fetchAll();

    $header = [
      'player' => 'Player',
      'country' => 'Country',
      'known_for' => 'Summary',
      'team' => 'Team',
    ];

    $rows = [];

    foreach($result as $record) {
      $data = unserialize($record->player_data);
      $country = $data['country'];
      $known_for = $data['known_for'];

      $rows[] = [
        $record->player_name,
        $country,
        $known_for,
        $record->team_name,
      ];
    }

    $build = [];
    $build[] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
    $build[] = [
      '#type' => 'pager',
    ];

    return $build;
  }

}
