<?php

namespace Drupal\hnp_light_dark_modes_toggle\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ThemeModeSwitcher extends ControllerBase {

  public function switchTo(string $mode = "") {
    /** @var Drupal\Core\Config\Config */
    $config = \Drupal::service('config.factory')->getEditable('hnp_light_dark_modes_toggle.current_theme_mode');
    $current_theme_mode = $config->get('current_theme_mode');
    if ($mode !== $current_theme_mode) {
      $config->set('current_theme_mode', $mode)->save();
    }
    return new JsonResponse([
      'message' => 'Successfully changed theme!',
    ]);
  }

}
