<?php

namespace Drupal\eg_csrf\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;

class ContactAuthor {

  public function landingPage() {
    $token = \Drupal::getContainer()->get('csrf_token')->get("csrf-contact-author");
    $url = Url::fromRoute('eg_csrf.contact_author', [], ['query' => ['token' => $token]]);
    $link = Link::fromTextAndUrl('Contact Author', $url);
    $build['link'] = $link->toRenderable();

    return $build;
  }

  public function contactAuthor() {
    // \Drupal::getContainer()->get('csrf_token')->validate($token, "csrf-contact-author");
    return [
      '#theme' => 'markup',
      '#markup' => '<h1>Contact Author</h1>',
    ];
  }

}
