<?php

namespace Drupal\surgery\Controller\Coffee;

use Drupal\surgery\Constants\Constants as K;
use Drupal\surgery\Utils\ClosestMatchesUtil;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListFieldsCoffeeCommandHandler {

  public function handle(string $query) {
    $value = '';
    $bundle = NULL;
    // Extracts the node ID or the bundle.
    $remainder_str = trim(str_replace(K::LIST_FIELDS_COMMAND . ' ', '', $query));
    if (is_numeric($remainder_str)) {
      $nid = $remainder_str;
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $bundle = $node->bundle();
    }
    else {
      $bundle = $remainder_str;
    }
    /** @var array $bundles */
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');

    $target_bundles = ClosestMatchesUtil::getClosestMatches($bundle, $bundles);
    $_bundle = reset($target_bundles);
    $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $_bundle);
    foreach ($field_definitions as $name => $field_definition) {
      if (str_contains($name, 'field_') || in_array($name, ['nid', 'title', 'langcode'])) {
        $value .= "<span class=\"coffee-field coffee--{$name}\">{$name}</span>";
      }
    }
    $value = "<div class=\"list-fields\"><div class=\"list-fields--click-on--value\"></div>{$value}</div>";
    $data = [
      'value' => $value,
      'message' => 'Success',
      'status' => TRUE,
    ];
    return new JsonResponse([
      'data' => $data,
    ]);
  }

}
