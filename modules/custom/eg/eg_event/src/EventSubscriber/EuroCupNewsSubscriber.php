<?php

namespace Drupal\eg_event\EventSubscriber;

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EuroCupNewsSubscriber implements EventSubscriberInterface {

  /**
   * @var Drupal\Core\Session\AccountProxyInterface $current_user.
   */
  protected $current_user;

  protected $current_route_match;

  /**
   * @param Drupal\Core\Session\AccountProxyInterface $current_user.
   */
  public function __construct(
    AccountProxyInterface $current_user,
    CurrentRouteMatch $current_route_match
  ) {
    $this->current_user = $current_user;
    $this->current_route_match = $current_route_match;
  }

  /**
   * Handler for the kernel event request.
   *
   * @param Symfony\Component\HttpKernel\Event\ResponseEvent $event.
   */
  public function on_request(RequestEvent $event) {
    $route_name = $this->current_route_match->getRouteName();

    // We target this page only. Use route name instead of path name.
    if ($route_name !== 'eg_event.euro_cup_news') {
      return;
    }

    // If user does not have non_graduate role, he is redirected to homepage.
    $roles = $this->current_user->getRoles();
    if (!in_array('non_graduate', $roles)) {
      $url = Url::fromUri('internal:/');
      $event->setResponse(new LocalRedirectResponse($url->toString()));
      \Drupal::messenger()->addMessage('You do not have Non-graduate role');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Do not hard code
    // $events['kernel.request'][] = ['on_request', 0];
    $events[KernelEvents::REQUEST] = ['on_request', 0];
    return $events;
  }

}
