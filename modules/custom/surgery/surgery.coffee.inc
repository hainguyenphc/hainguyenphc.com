<?php

use Drupal\Core\Url;

/**
 * Implements hook_coffee_commands().
 */
function surgery_coffee_commands() {
  $commands = [];

  // $url = \Drupal\Core\Url::fromRoute('view.content.page_1');
  $get_command = 'get';
  $edit_bundle_command = 'edit-bundle';
  $manage_fields_command = 'manage-fields';
  $manage_form_display_command = 'manage-form';
  $manage_displays_command = 'manage-displays';
  $manage_dependencies_command = 'manage-deps';
  //
  $list_fields_command = 'list-fields';

  /** @var Drupal\Core\Config\ImmutableConfig $system_site */
  $system_site = \Drupal::config('system.site');
  /** @var string $site_name */
  $site_name = $system_site->get('name');
  /** @var Drupal\Core\Entity\EntityTypeManager $entity_manager */
  $entity_manager = \Drupal::entityTypeManager();
  // Only use node types the user has access to.
  foreach ($entity_manager->getStorage('node_type')->loadMultiple() as $type) {
    $node_type = $type->id();
    if ($entity_manager->getAccessControlHandler('node')->createAccess($node_type)) {
      // ========================================
      // `get` command
      // ========================================
      $content_route = NULL;
      $node_type_label = $type->label();
      $site_name = strtolower($site_name);
      if (in_array($site_name, ['napoleon', 'kaufman hall'])) {
        $content_route = 'view.content.page_1';
      }
      if ($content_route) {
        $value = Url::fromRoute($content_route, [
          'type' => $node_type,
          'langcode' => 'en',
        ])->toString();
        $commands[] = [
          'value' => $value,
          'label' => $node_type_label,
          'command' => $get_command . ' ' . $node_type_label,
        ];
      }
      // ========================================
      // `edit-bundle` command
      // ========================================
      // ddev drush route --path=/en/admin/structure/types/manage/accessory 
      $value = Url::fromRoute('entity.node_type.edit_form', [
        'node_type' => $node_type,
      ])->toString(); 
      $commands[] = [
        'value' => $value,
        'label' => 'Edit ' . $node_type_label,
        'command' => $edit_bundle_command . ' ' . $node_type_label,
      ];
      // ========================================
      // `manage-fields` command
      // ========================================
      // ddev drush route --path=/en/admin/structure/types/manage/part/fields
      $value = Url::fromRoute('entity.node.field_ui_fields', [
        'node_type' => $node_type,
      ])->toString(); 
      $commands[] = [
        'value' => $value,
        'label' => 'Manage fields of ' . $node_type_label,
        'command' => $manage_fields_command . ' ' . $node_type_label,
      ];
      // ========================================
      // `manage-form` command
      // ========================================
      // ddev drush route --path=/en/admin/structure/types/manage/accessory/form-display
      $value = Url::fromRoute('entity.entity_form_display.node.default', [
        'node_type' => $node_type,
      ])->toString(); 
      $commands[] = [
        'value' => $value,
        'label' => 'Manage form display of ' . $node_type_label,
        'command' => $manage_form_display_command . ' ' . $node_type_label,
      ];
      // ========================================
      // `manage-displays` command
      // ========================================
      // ddev drush route --path=/en/admin/structure/types/manage/article/display
      $value = Url::fromRoute('entity.entity_view_display.node.default', [
        'node_type' => $node_type,
      ])->toString(); 
      $commands[] = [
        'value' => $value,
        'label' => 'Manage displays of ' . $node_type_label,
        'command' => $manage_displays_command . ' ' . $node_type_label,
      ];
      // ========================================
      // `manage-deps` command
      // ========================================
      if (\Drupal::moduleHandler()->moduleExists('conditional_fields')) {
        // ddev drush route --path=/en/admin/structure/types/manage/accessory/conditionals
        $value = Url::fromRoute('conditional_fields.tab.node', [
          'node_type' => $node_type,
        ])->toString(); 
        $commands[] = [
          'value' => $value,
          'label' => 'Manage dependencies of ' . $node_type_label,
          'command' => $manage_dependencies_command . ' ' . $node_type_label,
        ];
      }
      // ========================================
      // `list-fields` command
      // ========================================
      $commands[] = [
        'value' => 'list-fields ' . $node_type,
        'label' => 'List fields of bundle: ' . $node_type_label,
        'command' => $list_fields_command . ' ' . $node_type_label,
        'is_custom_command' => TRUE,
      ];
    }
  }

  return $commands;
}
