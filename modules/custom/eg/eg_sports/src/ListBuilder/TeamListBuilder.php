<?php

namespace Drupal\eg_sports\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

class TeamListBuilder extends EntityListBuilder {

  public function buildHeader() {
    $header['id'] = $this->t('Team ID');
    $header['name'] = $this->t('Name');
    $header = $header + parent::buildHeader();
    return $header;
  }

  public function buildRow(EntityInterface $entity) {
    /** @var Drupal\eg_products\Entity\Team $entity */
    $row['id'] = $entity->id();
    // Product canonical URL.
    // entity.[entity_type].canonical route
    $row['name'] = $entity->toLink();
    $row = $row + parent::buildRow($entity);
    return $row;
  }

}
