<?php

namespace Drupal\eg_salutation\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Route;

class HelloController extends ControllerBase {

  /**
   * Says a greeting.
   *
   * @return array
   *  render array with hello message.
   */
  public function hello() {
    // Redirects from a controller - this is the only place to look.
    // Other subclasses of RedirectResponse such as LocalRedirectResponse,
    // TrustedRedirectResponse (both extending SecuredRedirectResponse) are also
    // available.
    return new RedirectResponse('/node/1');
    // return [
    //   '#markup' => $this->t('Hello, how are you today?'),
    // ];
  }

  public function goodbye() {
    return [
      '#markup' => $this->t('Goodbye, take care of yourself!'),
    ];
  }

  /** 
   * @param \Drupal\Core\Session\AccountInterface $account
   * 
   * @return \Drupal\Core\Access\AccessResult
   */
  public function access(AccountInterface $account, Route $route, RouteMatch $routeMatch) {
    return in_array('editor', $account->getRoles()) ? AccessResult::forbidden() : AccessResult::allowed();
  }

}
