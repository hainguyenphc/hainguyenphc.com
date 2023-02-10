<?php

namespace Drupal\surgery\Controller\Coffee;

use Drupal\node\NodeInterface;
use Drupal\surgery\Constants\Constants as K;
use Symfony\Component\HttpFoundation\JsonResponse;

class InspectCoffeeCommandHandler {

  public function handle(string $query) {
    // Extracts the node ID.
    $nid = trim(str_replace(K::INSPECT_COMMAND . ' ', '', $query));
    if (is_numeric($nid)) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      if ($node instanceof NodeInterface) {
        $data = [
          'label' => $node->label(),
          'bundle' => $node->bundle(),
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