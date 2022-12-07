<?php

namespace Drupal\eg_products;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\eg_products\Entity\ProductInterface;

class ProductListBuilder extends EntityListBuilder {

  public function buildHeader() {
    $header['id'] = $this->t('Product ID');
    $header['name'] = $this->t('Name');
    $header = $header + parent::buildHeader();
    return $header;
  }

  public function buildRow(EntityInterface $entity) {
    /** @var Drupal\eg_products\Entity\Product $entity */
    $row['id'] = $entity->id();
    // Product canonical URL.
    // entity.[entity_type].canonical route
    $row['name'] = $entity->toLink();
    $row = $row + parent::buildRow($entity);
    return $row;
  }

}
