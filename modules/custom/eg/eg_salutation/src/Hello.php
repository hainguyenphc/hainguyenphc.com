<?php

namespace Drupal\eg_salutation;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\eg_salutation\HelloInterface;

class Hello implements HelloInterface {

  use StringTranslationTrait;

  protected $config_factory;

  /**
   * @var Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config_factory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function hello(AccountInterface $user = NULL) {
    $time = new \DateTime();
    $hour = (int) $time->format('G');
    $greeting = NULL;
    if (0 <= $hour && $hour < 12) {
      $greeting = 'Good Morning';
    }
    else if ($hour < 18) {
      $greeting = 'Good Afternoon';
    }
    else {
      $greeting = 'Good Evening';
    }
    $greeting .= ', ' . $user->getAccountName();

    // First approach, no DI is required.
    $config = \Drupal::config('eg_salutation.show_emoji_face');
    if ($config->get('show_emoji')) {
      $greeting .= ' :D';
    }

    // Second approach, DI is required.
    // @see services.yml file.
    $config = $this->config_factory->get('eg_salutation.supplement');
    $supplement = $config->get('supplement');
    if ($supplement && strlen($supplement) > 0) {
      $greeting .= ' ' . $supplement;
    }

    return $this->t($greeting);
  }

}
