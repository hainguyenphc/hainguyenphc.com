<?php

namespace Drupal\eg_salutation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class NodeIntroductionController extends ControllerBase {

  public function introduce($node_param) {
    $owner = $node_param->getOwner();
    $owner_name = $owner->name->value;
    $last_changed = $node_param->changed->value; // timestamp
    $last_changed = date('Y/F/j', $last_changed);
    $content = "Authored by $owner_name and last edited on $last_changed.";

    // Redirects from a controller - this is the only place to look.
    // The webpage is blank and displays this text.
    return new Response("A plain text on page");
    // return [
    //   '#markup' => $this->t($content),
    // ];
  }

  public function getTitle($node_param) {
    $title = 'Brief introduction to ' . $node_param->getTitle();
    return $this->t($title);
  }

}
