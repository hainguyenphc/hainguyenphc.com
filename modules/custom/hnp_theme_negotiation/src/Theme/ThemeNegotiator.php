<?php

namespace Drupal\hnp_theme_negotiation\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

class ThemeNegotiator implements ThemeNegotiatorInterface {

  const IS_DEBUGGING = FALSE;

  /** 
   * {@inheritDoc}
   */
  function applies(RouteMatchInterface $route_match) {
    return self::IS_DEBUGGING;
  }

  /** 
   * {@inheritDoc}
   */
  function determineActiveTheme(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();

    if ($route_name === 'node.add' || $route_name === 'entity.node.edit_form') {
      return 'claro';
    }
    else if ($route_name === 'entity.node.canonical') {
      if ($route_match->getParameters()->get('node')->toUrl()->toString() === '/home') {
        return 'reactportfolio';
      }
      return 'portfolio';
    }
    
    return 'claro';
  }

}
