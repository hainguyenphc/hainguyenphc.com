<?php

namespace Drupal\hnp_light_dark_modes_toggle\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides 'Light/Dark Toggle' block.
 *
 * @Block(
 *   id = "light_dark_toggle_block",
 *   admin_label = @Translation("Toggle Between Light/Dark modes"),
 * )
 */
class LightDarkModeToggle extends BlockBase {
  /**
   * {@inheritDoc}
   */
  public function build() {
    $build = [];

    $config = \Drupal::config('hnp_light_dark_modes_toggle.current_theme_mode');
    $current_theme_mode = $config->get('current_theme_mode');

    $build[] = [
      // '#type' => 'markup',
      // '#markup' => '<label class="light-dark-toggle"><input type="checkbox"/><span class="slider"></span></label>',
      // '#attached' => [
      //   'library' => [
      //     'hnp_light_dark_modes_toggle/light_dark_toggle',
      //   ],
      // ],
      '#theme' => 'light_dark_toggle',
      '#current_mode' => 'dark',
      '#attached' => [
        'drupalSettings' => [
          'current_theme_mode' => $current_theme_mode,
        ],
      ],
    ];

    return $build;
  }
}
