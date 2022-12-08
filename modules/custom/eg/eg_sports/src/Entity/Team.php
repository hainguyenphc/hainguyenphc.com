<?php

namespace Drupal\eg_sports\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * @ContentEntityType(
 *  id = "team",
 *  label = @Translation("Team"),
 *  bundle_label = @Translation("Team Type"),
 *  handlers = {
 *    "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *    "list_builder" = "Drupal\eg_sports\ListBuilder\TeamListBuilder",
 *    "form" = {
 *      "default" = "Drupal\eg_sports\Form\TeamForm",
 *      "add"     = "Drupal\eg_sports\Form\TeamForm",
 *      "edit"    = "Drupal\eg_sports\Form\TeamForm",
 *      "delete"  = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *    },
 *    "route_provider" = {
 *      "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *    },
 *  },
 *  base_table = "team",
 *  admin_permission = "administer site configuration",
 *  entity_keys = {
 *    "id" = "id",
 *    "label" = "name",
 *    "uuid" = "uuid",
 *    "bundle" = "type",
 *  },
 *  links = {
 *    "canonical"   = "/admin/structure/team/{team}",
 *    "add-form"    = "/admin/structure/team/add/{team_type}",
 *    "add-page"    = "/admin/structure/team/add",
 *    "edit-form"   = "/admin/structure/team/{team}/edit",
 *    "delete-form" = "/admin/structure/team/{team}/delete",
 *    "collection"  = "/admin/structure/team"
 *  },
 *  bundle_entity_type = "team_type",
 *  field_ui_base_route = "entity.team_type.edit_form"
 * )
 */
class Team extends ContentEntityBase implements TeamInterface {

  use EntityChangedTrait;

  public function getTeamName() {
    return $this->get('name')->value;
  }

  public function setTeamName($name) {
    $this->set('name', $name);
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Team Name'))
      ->setDescription(t('The team name'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
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

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Team Description'))
      ->setDescription(t('The team description'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
