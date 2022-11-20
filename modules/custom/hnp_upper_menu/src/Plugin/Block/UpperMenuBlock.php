<?php

namespace Drupal\hnp_upper_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides 'Upper Menu' block.
 *
 * @Block(
 *   id = "upper_menu_block",
 *   admin_label = @Translation("Upper Menu Block"),
 *   category = @Translation("Menu Block"),
 * )
 */
class UpperMenuBlock extends BlockBase {

  /** {@inheritDoc} */
  public function build() {
    $build = [];

    $build['drupal'] = [
      '#type' => 'link',
      '#url' => Url::fromUri(''),
      '#title' => $this->t('Drupal'),
      '#attributes' => [
        'id' => '',
        'class' => [],
      ],
    ];

    return $build;
  }

}