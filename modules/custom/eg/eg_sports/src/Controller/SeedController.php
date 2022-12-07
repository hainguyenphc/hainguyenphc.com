<?php

namespace Drupal\eg_sports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Exception;

class SeedController extends ControllerBase {

  protected static $table_teams = 'eg_sports_teams';

  protected static $table_players = 'eg_sports_players';

  public function seed() {
    $database = \Drupal::database();

    $teams = [
      [
        'name' => $this->t('Real Madrid'),
        'description' => $this->t('Real Madrid Club de Fútbol, commonly referred to as Real Madrid, is a Spanish professional football club based in Madrid. Founded on 6 March 1902 as Madrid Football Club, the club has traditionally worn a white home kit since inception'),
      ],
      [
        'name' => $this->t('FC Barcelona'),
        'description' => $this->t('Futbol Club Barcelona, commonly referred to as Barcelona and colloquially known as Barça, is a Spanish professional football club based in Barcelona, Spain, that competes in La Liga, the top flight of Spanish football.'),
      ]
    ];

    $players = [
      [
        'team_id' => 1,
        'name' => 'Gareth Bale',
        'data' => serialize([
          'country' => $this->t('Wales'),
          'known_for' => $this->t('Bale made his senior international debut for Wales in May 2006, becoming the youngest player at that point to represent Wales. He has since earned over 90 caps and scored 33 international goals, making him Wales\' highest scorer of all time'),
        ])
      ],
      [
        'team_id' => 1,
        'name' => 'Toni Kroos',
        'data' => serialize([
          'country' => $this->t('Germany'),
          'known_for' => $this->t('His trophy cabinet includes a Champions League trophy, 3 Bundesliga titles, 1 FIFA Clubs World Cup, 3 DFB-Pokal trophies, 1 German Super Cup and a UEFA Supercup as well.'),
        ])
      ],
      [
        'team_id' => 2,
        'name' => 'Memphis Depay',
        'data' => serialize([
          'country' => $this->t('The Netherlands'),
          'known_for' => $this->t('Memphis Depay, also known simply as Memphis, is a Dutch professional footballer who plays as a forward for La Liga club Barcelona and the Netherlands national team.'),
        ])
      ],
      [
        'team_id' => 2,
        'name' => 'Antoine Griezmann',
        'data' => serialize([
          'country' => $this->t('France'),
          'known_for' => $this->t('Antoine Griezmann is a French professional footballer who plays as a forward for La Liga club Barcelona and the France national team. Griezmann began his senior club career playing for Real Sociedad, and won the Segunda División title in his first season.'),
        ])
      ],
    ];

    $transaction = $database->startTransaction();
    try {
      $fields = ['name', 'description'];
      $query = $database->insert(SeedController::$table_teams)->fields($fields);
      foreach($teams as $team) {
        $query->values($team);
      }
      $result = $query->execute();

      $fields = ['team_id', 'name', 'data'];
      $query = $database->insert(SeedController::$table_players)->fields($fields);
      foreach($players as $player) {
        $query->values($player);
      }
      $result = $query->execute();

      $content = $this->t('The teams and players tables are populated.');
      return [
        '#markup' => "<h1>$content</h1>",
      ];
    }
    catch (Exception $ex) {
      $transaction->rollBack();
      watchdog_exception('my_type', $ex);
    }
    // You can let $transaction go out of scope here and the transaction will
    // automatically be committed if it wasn't already rolled back.
    // However, if you have more work to do, you will want to commit the transaction
    // yourself, like this:
    unset($transaction);

    $content = $this->t('Something went wrong.');
    return [
      '#markup' => "<h1>$content</h1>",
    ];
  }

}
