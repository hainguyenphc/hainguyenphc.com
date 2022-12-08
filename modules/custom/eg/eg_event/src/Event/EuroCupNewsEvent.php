<?php

namespace Drupal\eg_event\Event;

use Symfony\Contracts\EventDispatcher\Event as EventDispatcherEvent;

class EuroCupNewsEvent extends EventDispatcherEvent {

  const EURO_CUP_EVENT = 'eg_event.event.euro_cup_news';

  /* @var mixed */
  protected $message;

  /**
   * Our custom accessor, the data type of $message could be anything.
   *
   * @return mixed
   */
  public function getValue() {
    return $this->message;
  }

  /**
   * Setter for other modules to intercept the message.
   */
  public function setValue($message) {
    $this->message = $message;
  }

}
