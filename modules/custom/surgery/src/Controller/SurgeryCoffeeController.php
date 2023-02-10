<?php

namespace Drupal\surgery\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\surgery\Constants\Constants as K;
use Drupal\surgery\Controller\Coffee\FieldValueCoffeeCommandHandler;
use Drupal\surgery\Controller\Coffee\InspectCoffeeCommandHandler;
use Drupal\surgery\Controller\Coffee\ListFieldsCoffeeCommandHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

class SurgeryCoffeeController extends ControllerBase {

  public function coffeeData(string $query = "") {
    /**
     * Dynamic commands.
     * 1. inspect NID
     *    E.g., "inspect 198061" to load a node dynamically.
     * 2. field-value NID FIELD_MACHINE_NAME
     *    E.g., "field-value 198061 ref" to load all fields whose names contain `ref` for the node.
     * 3. list-fields NID|BUNDLE
     *    E.g., "list-fields 100" or "list-fields product".
     * @todo add more commands.
     */

    $inspect_pattern = '/' . K::INSPECT_COMMAND . ' \d+/i';
    
    $field_value_pattern = '/' . K::FIELD_VALUE_COMMAND . ' \d+/i';
  
    $list_fields_pattern = '/' . K::LIST_FIELDS_COMMAND . ' \w+/i';

    if (preg_match($inspect_pattern, $query, $matches)) {
      return (new InspectCoffeeCommandHandler())->handle($query);
    }
    else if (preg_match($field_value_pattern, $query, $matches)) {
      return (new FieldValueCoffeeCommandHandler())->handle($query);
    }
    else if (preg_match($list_fields_pattern, $query, $matches)) {
      return (new ListFieldsCoffeeCommandHandler())->handle($query);
    }
    else {
      // A fallback response.
      return new JsonResponse([
        'data' => [
          'message' => 'Error',
          'status' => FALSE,
        ]
      ]);
    }

    /**
     * Static commands.
     */

    $output = [];

    $output[] = [
      'value' => 'dummy',
      'label' => 'dummy',
      'command' => 'dummy',
    ];

    // A test result.
    /** @var Drupal\Core\Config\ImmutableConfig $system_site */
    $system_site = \Drupal::config('system.site');
    /** @var string $site_name */
    $site_name = strtolower($system_site->get('name'));

    // Extends Napoleon.
    if ($site_name === 'napoleon') {
    }

    return new JsonResponse($output);
  }

  private function extendNapoleon() {}

}
