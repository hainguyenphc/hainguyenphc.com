<?php

namespace Drupal\eg_salutation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

}
