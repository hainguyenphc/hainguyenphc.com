<?php

namespace Drupal\eg_products\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Url;

/**
 * Defines the Importer entity - config entity.
 *
 * @ConfigEntityType(
 *  id = "importer",
 *  label = @Translation("Importer"),
 *  handlers = {
 *    "list_builder" = "Drupal\eg_products\ImporterListBuilder",
 *    "form" = {
 *      "add"    = "Drupal\eg_products\Form\ImporterForm",
 *      "edit"   = "Drupal\eg_products\Form\ImporterForm",
 *      "delete" = "Drupal\eg_products\Form\ImporterDeleteForm",
 *    },
 *    "route_provider" = {
 *      "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *    }
 *  },
 *  config_prefix = "importer",
 *  admin_permission = "administer site configuration",
 *  entity_keys = {
 *    "id" = "id",
 *    "label" = "label",
 *    "uuid" = "uuid"
 *  },
 *  links = {
 *    "add-form"    = "/admin/structure/importer/add",
 *    "edit-form"   = "/admin/structure/importer/{importer}/edit",
 *    "delete-form" = "/admin/structure/importer/{importer}/delete",
 *    "collection"  = "/admin/structure/importer"
 *  },
 *  config_export = {
 *    "id",
 *    "label",
 *    "source_url",
 *    "plugin_id",
 *    "update_existing",
 *    "source",
 *    "bundle"
 *  }
 * )
 */
class Importer extends ConfigEntityBase implements ImporterInterface {

  /**
   * @var string the Importer ID.
   */
  protected $id;

  /**
   * @var string the Importer label
   */
  protected $label;

  /**
   * @var string the Plugin ID of the plugin used for importing products.
   */
  protected $plugin_id;

  /**
   * @var bool
   */
  protected $update_existing = TRUE;

  /**
   * @var string the URL from where the import file could be retrieved.
   */
  protected $source_url;

  /**
   * @var string the source of products.
   */
  protected $source;

  /**
   * @var string The product type (bundle).
   */
  protected $bundle;

  /**
   * {@inheritdoc}
   */
  public function getSourceUrl() {
    return ($this->source_url) ? Url::fromUri($this->source_url) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin_id;
  }

  /**
   * {@inheritdoc}
   */
  public function updateExistingProducts() {
    return $this->update_existing;
  }

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    return $this->source;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundle() {
    return $this->bundle;
  }

}
