<?php

namespace Drupal\eg_menux\Controller;

use Drupal\Core\Controller\ControllerBase;

class MenuxController extends ControllerBase {

  public function render() {
    return [
      '#markup' => $this->t('Menux'),
    ];
  }

}
