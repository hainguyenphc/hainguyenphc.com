<?php

namespace Drupal\eg_event;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\eg_event\Event\EuroCupNewsEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EuroCupHistory {

  use StringTranslationTrait;

  /**
   * @var Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher.
   */
  protected $event_dispatcher;

  public function __construct(EventDispatcherInterface $event_dispatcher) {
    $this->event_dispatcher = $event_dispatcher;
  }

  public function getHistory() {
    // Before preparing the history, this fires the event.
    // The subscribers should listen to EuroCupNewsEvent::EURO_CUP_EVENT.
    // @see EuroCupNewsSubscriber for an example.
    $event = new EuroCupNewsEvent();
    $value = [
      'datetime' => date('Y/F/d'),
      'user' => \Drupal::currentUser(),
      'comment' => t('see EuroCupHistory class'),
    ];
    $event->setValue($value);
    $event = $this->event_dispatcher
      ->dispatch(EuroCupNewsEvent::EURO_CUP_EVENT, $event);

    // Prepares the content as usual for use in EuroCupNews controller.
    $content = "<p>The UEFA European Football Championship, less formally, the European Championship (EC) and informally as the Euros, is the primary association football competition contested by the senior men's national teams of the members of the Union of European Football Associations (UEFA), determining the continental champion of Europe.</p>";
    return $this->t($content);
  }

}
