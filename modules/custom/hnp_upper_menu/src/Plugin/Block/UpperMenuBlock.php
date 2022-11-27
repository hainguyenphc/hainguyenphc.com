<?php

namespace Drupal\hnp_upper_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\hnp_constants\Constants;

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

  /** 
   * {@inheritDoc} 
   */
  public function build() {
    /** @var array $build */
    $build = [];

    // WEB DEV

    $build['web']['drupal_7'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute(Constants::DEFAULT_ROUTE),
      '#title' => $this->t('Drupal 7'),
      '#attributes' => [
        'id' => 'web--drupal-7',
        'class' => [],
      ],
    ];

    $build['web']['drupal_9'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute(Constants::DEFAULT_ROUTE),
      '#title' => $this->t('Drupal 9+'),
      '#attributes' => [
        'id' => 'web--drupal-9',
        'class' => [],
      ],
    ];

    // MOBILE DEV

    $build['mobile']['ios'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute(Constants::DEFAULT_ROUTE),
      '#title' => $this->t('iOS'),
      '#attributes' => [
        'id' => 'mobile--ios',
        'class' => [],
      ],
    ];

    // GENERAL

    $build['general']['resume'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute(Constants::DEFAULT_ROUTE),
      '#title' => $this->t('My Resume'),
      '#attributes' => [
        'id' => 'general--resume',
        'class' => [],
      ],
    ];

    $build['general']['contact'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute(Constants::DEFAULT_ROUTE),
      '#title' => $this->t('Say Hi'),
      '#attributes' => [
        'id' => 'general--contact',
        'class' => [],
      ],
    ];

    return $build;
  }

}

/*
@TODO: replace the hard-code route Constants::DEFAULT_ROUTE.
*/
