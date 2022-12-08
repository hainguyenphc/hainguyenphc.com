<?php

namespace Drupal\eg_link\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

class LinkDemo extends ControllerBase {

  public function renderPage(Request $request) {
    $content = "<h3>Generate a link using the 'link_generator' service</h3>";
    $content .= "<code>\$url = Url::fromRoute('route_machine_name', ['param_name' => \$param_value])</code>";
    $content .= "<br/>";
    $content .= "<code>\$link = \Drupal::service('link_generator')->generate('Link Text', \$url);</code>";
    $content .= "<br/>";
    $content .= "<p>The other way</p>";
    $content .= "<code>\$url = Url::fromRoute('route_machine_name', ['param_name' => \$param_value])</code>";
    $content .= "<br/>";
    $content .= "<code>\$link = Link::fromTextAndUrl('Link Text', \$url);</code>";
    $content .= "<br/>";
    $content .= "<code>\$link = \Drupal::service('link_generator')->generateFromLink(\$link);</code>";
    $content .= "<br/>";
    $content .= "<h3>Examples</h3>";
    $examples = "<ul>";
    $routes = [
      'eg_salutation.hello',
      'eg_salutation.hello_config_form',
      'eg_salutation.hello_ws', // /hello-ws/{user}
      'eg_salutation.introduce_node', // /introduce/{node_param}
    ];
    for ($i = 0; $i < 4; $i++) {
      if ($i == 2) {
        $url = Url::fromRoute($routes[$i], ['user' => 1]);
      }
      else if ($i == 3) {
        $url = Url::fromRoute($routes[$i], ['node_param' => 1]);
      }
      else {
        $url = Url::fromRoute($routes[$i]);
      }
      $link = \Drupal::service('link_generator')->generate($routes[$i], $url);
      $examples .= "<li>" . $routes[$i] . " - " . $link . "</li>";
    }
    $examples .= "</ul>";
    $content .= $examples;
    return [
      '#markup' => $content,
    ];
  }

}
