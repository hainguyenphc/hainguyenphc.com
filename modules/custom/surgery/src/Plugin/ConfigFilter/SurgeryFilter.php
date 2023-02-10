<?php

namespace Drupal\surgery\Plugin\ConfigFilter;

use Drupal\config_filter\Plugin\ConfigFilterBase;

/**
 * @ConfigFilter(
 *   id = "surgery_filter",
 *   label = @Translation("Surgery Filter"),
 *   weight = 150
 * )
 */
class SurgeryFilter extends ConfigFilterBase {

  /**
   * {@inheritDoc}
   */
  public function filterWrite($name, array $data) {
    if ($name === 'core.extension') {
      $modules = &$data['module'];
      if (array_key_exists('coffee', $modules)) {
        unset($modules['coffee']);
      }
      if (array_key_exists('surgery', $modules)) {
        unset($modules['surgery']);
      }
    }
    else if ($name === 'coffee.configuration') {
      return null;
    }

    return $data;
  }

}