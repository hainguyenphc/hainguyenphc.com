<?php

namespace Drupal\eg_sports\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * @ConfigEntityType(
 *  id = "team_type",
 *  label = @Translation("Team Type"),
 *  handlers = {
 *    "list_builder" = "Drupal\eg_sports\ListBuilder\TeamTypeListBuilder",
 *    "form" = {
 *      "add"    = "Drupal\eg_sports\Form\TeamTypeForm",
 *      "edit"   = "Drupal\eg_sports\Form\TeamTypeForm",
 *      "delete" = "Drupal\eg_sports\Form\TeamTypeDeleteForm"
 *    },
 *    "route_provider" = {
 *      "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *    }
 *  },
 *  config_prefix = "team_type",
 *  admin_permission = "administer site configuration",
 *  bundle_of = "team",
 *  entity_keys = {
 *    "id" = "id",
 *    "label" = "label",
 *    "uuid" = "uuid"
 *  },
 *  links = {
 *    "canonical"   = "/admin/structure/team_type/{team_type}",
 *    "add-form"    = "/admin/structure/team_type/add",
 *    "edit-form"   = "/admin/structure/team_type/{team_type}/edit",
 *    "delete-form" = "/admin/structure/team_type/{team_type}/delete",
 *    "collection"  = "/admin/structure/team_type"
 *  },
 *  config_export = {
 *    "id",
 *    "label"
 *  }
 * )
 */
class TeamType extends ConfigEntityBundleBase implements TeamTypeInterface {

  /** @var string The ID of a team bundle. */
  protected $id;

  /** @var string The label of a team bundle. */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function isSinglePlayer() {
    return $this->single_player;
  }

}
