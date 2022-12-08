<?php

namespace Drupal\eg_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EuroCupNewsController extends ControllerBase {

  /**
   * @var Drupal\eg_event\EuroCupHistory $euro_cup_history.
   */
  protected $euro_cup_history;

  public function __construct($euro_cup_history) {
    $this->euro_cup_history = $euro_cup_history;
  }

  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('eg_event.euro_cup_history'),
    );
  }

  public function showNews() {
    $content = '<p>This page is meant for users with <strong>non_graduate</strong> role only.</p>';
    $history = $this->euro_cup_history->getHistory();
    $content .= $history;
    return [
      '#markup' => $this->t($content),
    ];
  }

}
