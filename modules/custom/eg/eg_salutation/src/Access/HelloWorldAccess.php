<?php

namespace Drupal\eg_salutation\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

class HelloWorldAccess implements AccessInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * @param AccountInterface $account
   * 
   * @return AccessResult
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowed();
  }

}