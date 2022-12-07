<?php

namespace Drupal\eg_products\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Represents a Product entity.
 *
 * EntityChangedInterface offers functionalities to deal with last changed date.
 * Those are to be added to Product class via `use EntityChangedTrait`.
 */
interface ProductInterface extends ContentEntityInterface, EntityChangedInterface {

  public function getName();

  public function setName($name);

  public function getProductNumber();

  public function setProductNumber($product_number);

  public function getRemoteId();

  public function setRemoteId($remote_id);

  public function getSource();

  public function setSource($source);

  public function getCreatedTime();

  public function setCreatedTime($created_time);

}
