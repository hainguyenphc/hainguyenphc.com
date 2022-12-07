<?php

namespace Drupal\eg_event\EventSubscriber;

use Drupal;
use Drupal\eg_event\Event\EuroCupNewsEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class EuroCupHistorySubscriber implements EventSubscriberInterface {

  public function on_reqest(EuroCupNewsEvent $event) {
  // Since EuroCupNewsEvent extends Event
  // public function on_reqest(Event $event) {
    $route_name = \Drupal::routeMatch()->getRouteName();

    if ($route_name !== 'eg_event.euro_cup_news') {
      return;
    }

    Drupal::messenger()->addWarning($event->getValue()['comment']);
    // Feel free to use $event->setValue(), $event->getValue(), etc.
  }

  public static function getSubscribedEvents() {
    $events[EuroCupNewsEvent::EURO_CUP_EVENT] = ['on_reqest', 0];
    return $events;
  }

}
