<?php

namespace Drupal\eg_products\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 *  @ContentEntityType(
 *    id = "product",
 *    label = @Translation("Product"),
 *    bundle_label = @Translation("Product Type"),
 *    handlers = {
 *      "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *      "list_builder" = "Drupal\eg_products\ProductListBuilder",
 *      "form" = {
 *        "default" = "Drupal\eg_products\Form\ProductForm",
 *        "add"     = "Drupal\eg_products\Form\ProductForm",
 *        "edit"    = "Drupal\eg_products\Form\ProductForm",
 *        "delete"  = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *      },
 *      "route_provider" = {
 *        "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *      },
 *    },
 *    base_table = "product",
 *    admin_permission = "administer site configuration",
 *    entity_keys = {
 *      "id" = "id",
 *      "label" = "name",
 *      "uuid" = "uuid",
 *      "bundle" = "type",
 *    },
 *    links = {
 *      "canonical"   = "/admin/structure/product/{product}",
 *      "add-form"    = "/admin/structure/product/add/{product_type}",
 *      "add-page"    = "/admin/structure/product/add",
 *      "edit-form"   = "/admin/structure/product/{product}/edit",
 *      "delete-form" = "/admin/structure/product/{product}/delete",
 *      "collection"  = "/admin/structure/product",
 *    },
 *    bundle_entity_type = "product_type",
 *    field_ui_base_route = "entity.product_type.edit_form"
 *  )
 */
class Product extends ContentEntityBase implements ProductInterface {

  use EntityChangedTrait;

  public function getName() {
    return $this->get('name')->value;
  }

  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  public function getProductNumber() {
    return $this->get('number')->value;
  }

  public function setProductNumber($product_number) {
    $this->set('number', $product_number);
    return $this;
  }

  public function getRemoteId() {
    return $this->get('remote_id')->value;
  }

  public function setRemoteId($remote_id) {
    $this->set('remote_id', $remote_id);
    return $this;
  }

  public function getSource() {
    return $this->get('source')->value;
  }

  public function setSource($source) {
    $this->set('source', $source);
    return $this;
  }

  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  public function setCreatedTime($created_time) {
    $this->set('created', $created_time);
    return $this;
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    /** 'string' type is a FieldType plugin. */
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The product name'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('fallback_product_name')
      /** FieldFormatter plugin */
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      /** FieldWidget plugin */
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['number'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Number'))
      ->setDescription(t('The Product number.'))
      ->setSettings([
        'min' => 1,
        'max' => 10000,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_unformatted',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['remote_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Remote ID'))
      ->setDescription(t('The remote ID of the Product.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('');

    $fields['source'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Source'))
      ->setDescription(t('The source of the Product.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
