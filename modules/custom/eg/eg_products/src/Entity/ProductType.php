<?php

namespace Drupal\eg_products\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * @ConfigEntityType(
 *  id = "product_type",
 *  label = @Translation("Product Type"),
 *  handlers = {
 *    "list_builder" = "Drupal\eg_products\ProductTypeListBuilder",
 *    "form" = {
 *      "add"    = "Drupal\eg_products\Form\ProductTypeForm",
 *      "edit"   = "Drupal\eg_products\Form\ProductTypeForm",
 *      "delete" = "Drupal\eg_products\Form\ProductTypeDeleteForm"
 *    },
 *    "route_provider" = {
 *      "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *    }
 *  },
 *  config_prefix = "product_type",
 *  admin_permission = "administer site configuration",
 *  bundle_of = "product",
 *  entity_keys = {
 *    "id" = "id",
 *    "label" = "label",
 *    "uuid" = "uuid"
 *  },
 *  links = {
 *    "canonical"   = "/admin/structure/product_type/{product_type}",
 *    "add-form"    = "/admin/structure/product_type/add",
 *    "edit-form"   = "/admin/structure/product_type/{product_type}/edit",
 *    "delete-form" = "/admin/structure/product_type/{product_type}/delete",
 *    "collection"  = "/admin/structure/product_type",
 *  },
 *  config_export = {
 *    "id",
 *    "label"
 *  }
 * )
 */
class ProductType extends ConfigEntityBundleBase implements ProductTypeInterface {

  /**
   * @var string The product type Id.
   */
  protected $id;

  /**
   * @var string The product type label.
   */
  protected $label;

}
