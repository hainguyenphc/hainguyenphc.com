<?php

namespace Drupal\eg_products\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an Importer item annotation object.
 *
 * @see \Drupal\eg_products\Plugin\ImporterPluginManager
 *
 * @Annotation
 */
class Importer extends Plugin {

  /** @var string */
  public $id;

  /** @var string */
  public $label;

}
