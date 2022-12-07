<?php

namespace Drupal\eg_salutation;

use Drupal\Core\Session\AccountInterface;

interface HelloInterface {

  function hello(AccountInterface $user = NULL);

}
