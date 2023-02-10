<?php

namespace Drupal\surgery\Controller\Coffee;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Render\Markup;
use Drupal\node\NodeInterface;
use Drupal\surgery\Constants\Constants as K;
use Drupal\surgery\Utils\ClosestMatchesUtil;
use Symfony\Component\HttpFoundation\JsonResponse;

class FieldValueCoffeeCommandHandler {

  public function handle(string $query) {
    // Extracts the node ID and the field machine name.
    $remainder_str = trim(str_replace(K::FIELD_VALUE_COMMAND . ' ', '', $query));
    $storage = explode(' ', $remainder_str);
    $nid = $storage[0];
    $field_machine_name = $storage[1];
    if (is_numeric($nid)) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      if ($node instanceof NodeInterface) {
        $field_definitions = $node->getFieldDefinitions();
        $names = $field_machine_name === '*'
          ? array_keys($field_definitions)
          : ClosestMatchesUtil::getClosestMatches($field_machine_name, $field_definitions);
        $value = [];
        foreach ($field_definitions as $name => $field_definition) {
          if (in_array($name, $names)) {
            $field_machine_name = $name;
            $value_ = $node->{$field_machine_name}->value;
            if (is_null($value)) $value_ = 'No value.';
            if ($node->{$field_machine_name} instanceof EntityReferenceFieldItemList) {
              $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
              $storage = \Drupal::entityTypeManager()->getStorage('node');
              $view = $view_builder->viewField($node->get($field_machine_name));
              $markup = \Drupal::service('renderer')->renderPlain($view);
              $value_ = $markup instanceof Markup ? $markup->__toString() : $markup;
            }
            $value_ = "<h5>{$field_machine_name}</h5><hr/>{$value_}";
            $value[] = $value_;
          }
        }
        $value = implode('', $value);
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
  }

}