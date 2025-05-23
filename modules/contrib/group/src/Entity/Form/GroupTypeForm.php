<?php

namespace Drupal\group\Entity\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Entity\Storage\GroupRoleStorageInterface;
use Drupal\group\PermissionScopeInterface;
use Drupal\user\RoleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for group type forms.
 */
class GroupTypeForm extends BundleEntityFormBase {

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new GroupTypeForm.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager service.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    assert($this->entity instanceof GroupTypeInterface);
    $form = parent::form($form, $form_state);
    $type = $this->entity;

    if ($this->operation === 'add') {
      $fields = $this->entityFieldManager->getBaseFieldDefinitions('group');
    }
    else {
      $fields = $this->entityFieldManager->getFieldDefinitions('group', $type->id());
    }

    $form['label'] = [
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->label(),
      '#description' => $this->t('The human-readable name of this group type. This text will be displayed as part of the list on the %group-add page. This name must be unique.', [
        '%group-add' => $this->t('Add group'),
      ]),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => GroupTypeInterface::ID_MAX_LENGTH,
      '#machine_name' => [
        'exists' => ['Drupal\group\Entity\GroupType', 'load'],
        'source' => ['label'],
      ],
      '#description' => $this->t('A unique machine-readable name for this group type. It must only contain lowercase letters, numbers, and underscores. This name will be used for constructing the URL of the %group-add page, in which underscores will be converted into hyphens.', [
        '%group-add' => $this->t('Add group'),
      ]),
    ];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->getDescription(),
      '#description' => $this->t('This text will be displayed on the <em>Add group</em> page.'),
    ];

    $form['group_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Group settings'),
      '#description' => $this->t('The following settings apply to all groups of this type.'),
      '#open' => FALSE,
    ];

    $form['group_settings']['title_label'] = [
      '#title' => $this->t('Title field label'),
      '#type' => 'textfield',
      '#default_value' => $fields['label']->getLabel(),
      '#description' => $this->t('Sets the label of the field that will be used for group titles.'),
      '#required' => TRUE,
    ];

    $form['group_settings']['new_revision'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create a new revision when a group is modified'),
      '#default_value' => $type->shouldCreateNewRevision(),
    ];

    $form['creator_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Creator settings'),
      '#open' => TRUE,
    ];

    $form['creator_settings']['creator_membership'] = [
      '#title' => $this->t('The group creator automatically becomes a member'),
      '#type' => 'checkbox',
      '#default_value' => $type->creatorGetsMembership(),
      '#description' => $this->t('This will make sure that anyone who creates a group of this type will automatically become a member of it.'),
    ];

    $form['creator_settings']['creator_wizard'] = [
      '#title' => $this->t('Group creator must complete their membership'),
      '#type' => 'checkbox',
      '#default_value' => $type->creatorMustCompleteMembership(),
      '#description' => $this->t('This will first show you the form to create the group and then a form to fill out your membership details.<br />You can choose to disable this wizard if you did not or will not add any fields to the membership.<br /><strong>Warning:</strong> If you do have fields on the membership and do not use the wizard, you may end up with required fields not being filled out.'),
      '#states' => [
        'visible' => [':input[name="creator_membership"]' => ['checked' => TRUE]],
      ],
    ];

    $access_information = $this->t('
      <p>Please note that Drupal accounts, <em><strong>including user 1</strong></em>, do not have access to any groups unless configured so.<br />It is therefore important that you at the very least do one of the following:</p>
      <ul>
        <li>Allow the group creator to get a role and set some permissions on it. You can edit this group type at any time to set more creator roles.</li>
        <li>Allow certain global roles to get some permissions via Outsider or Insider group roles.</li>
        <li>Optionally set an admin role, either for the group creator or for global roles (via Outsider or Insider group roles).</li>
      </ul>
      <p>If your use case does not require the group creator to be an admin of the group, then it is <strong>strongly advised</strong> to at least create an Outsider and/or Insider group role that synchronize with whatever adminstrator global role you have configured.</p>
    ');

    $form['access_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Access settings'),
      '#description' => $access_information,
      '#open' => TRUE,
    ];

    // Add-form specific elements.
    if ($this->operation == 'add') {
      $form['access_settings']['add_default_roles'] = [
        '#title' => $this->t('Automatically configure useful default roles'),
        '#type' => 'checkbox',
        '#default_value' => 0,
        '#description' => $this->t("This will create an 'Anonymous', 'Outsider' and 'Member' role by default which will synchronize to the 'Anonymous user' and 'Authenticated user' global roles."),
      ];

      $form['access_settings']['add_admin_role'] = [
        '#title' => $this->t('Automatically configure an administrative role'),
        '#type' => 'checkbox',
        '#default_value' => 0,
        '#description' => $this->t("This will create an 'Admin' role by default which will have all permissions."),
      ];

      $form['access_settings']['assign_admin_role'] = [
        '#title' => $this->t('Automatically assign this administrative role to group creators'),
        '#type' => 'checkbox',
        '#default_value' => 0,
        '#description' => $this->t("This will assign the 'Admin' role to the group creator membership."),
        '#states' => [
          'visible' => [
            ':input[name="creator_membership"]' => ['unchecked' => FALSE],
            ':input[name="add_admin_role"]' => ['unchecked' => FALSE],
          ],
        ],
      ];

      $admin_role = FALSE;
      foreach ($this->entityTypeManager->getStorage('user_role')->loadMultiple() as $user_role) {
        assert($user_role instanceof RoleInterface);
        if ($user_role->isAdmin()) {
          $admin_role = $user_role;
          break;
        }
      }

      if ($admin_role !== FALSE) {
        $form['access_settings']['global_admins'] = [
          '#type' => 'details',
          '#title' => $this->t('Global administrator settings'),
          '#description' => $this->t('<p>We have detected that your site has an all-access global role called @role.</p>', ['@role' => $admin_role->toLink(NULL, 'edit-form')->toString()]),
          '#open' => TRUE,
        ];

        $form['access_settings']['global_admins']['global_admin_role_id'] = [
          '#type' => 'value',
          '#value' => $admin_role->id(),
        ];

        $form['access_settings']['global_admins']['add_admin_outsider'] = [
          '#title' => $this->t('Automatically allow accounts with this role to manage all groups of this type they <strong>are not</strong> a member of'),
          '#type' => 'checkbox',
          '#default_value' => 1,
          '#description' => $this->t("This will create an 'Administrator' role in the Outsider scope that syncs to the global role."),
        ];

        $form['access_settings']['global_admins']['add_admin_insider'] = [
          '#title' => $this->t('Automatically allow accounts with this role to manage all groups of this type they <strong>are</strong> a member of'),
          '#type' => 'checkbox',
          '#default_value' => 1,
          '#description' => $this->t("This will create an 'Administrator' role in the Insider scope that syncs to the global role."),
        ];
      }
    }
    // Edit-form specific elements.
    else {
      $options = [];
      foreach ($type->getRoles(FALSE) as $group_role) {
        $options[$group_role->id()] = $group_role->label();
      }

      $form['access_settings']['creator_roles'] = [
        '#title' => $this->t('Group creator roles'),
        '#type' => 'checkboxes',
        '#options' => $options,
        '#default_value' => $type->getCreatorRoleIds(),
        '#description' => $this->t('Please select which custom group roles a group creator will receive.'),
        '#states' => [
          'visible' => [':input[name="creator_membership"]' => ['checked' => TRUE]],
        ],
      ];

      if (empty($options)) {
        $add_role_url = Url::fromRoute('entity.group_role.add_form', ['group_type' => $type->id()]);
        $t_args = ['@url' => $add_role_url->toString()];
        $description = $this->t('You do not have any custom group roles yet, <a href="@url">create one here</a>.', $t_args);
        $form['access_settings']['creator_roles']['#description'] .= "<br /><em>$description</em>";
      }
    }

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save group type');
    $actions['delete']['#value'] = $this->t('Delete group type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = trim($form_state->getValue('id'));
    // '0' is invalid, since elsewhere we might check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName('id', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", ['%invalid' => $id]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    assert($this->entity instanceof GroupTypeInterface);
    $group_type = $this->entity;
    $group_type_id = $group_type->id();

    // Trim any whitespace off the label.
    $group_type->set('label', trim($group_type->label()));

    // Clean up the creator role IDs as it comes from a checkboxes element.
    if ($creator_roles = $group_type->getCreatorRoleIds()) {
      $group_type->set('creator_roles', array_values(array_filter($creator_roles)));
    }

    $status = $group_type->save();
    $t_args = ['%label' => $group_type->label()];

    // Update title field definition.
    $fields = $this->entityFieldManager->getFieldDefinitions('group', $group_type_id);
    $title_field = $fields['label'];
    $title_label = $form_state->getValue('title_label');
    if ($title_field->getLabel() !== $title_label) {
      $title_field->getConfig($group_type_id)->setLabel($title_label)->save();
    }

    if ($status == SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('The group type %label has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('The group type %label has been added. You may now configure which roles a group creator will receive by editing the group type.', $t_args));
      $context = array_merge($t_args, ['link' => $group_type->toLink($this->t('View'), 'collection')->toString()]);
      $this->logger('group')->notice('Added group type %label.', $context);

      // Optionally create the default and/or admin roles.
      $add_default_roles = $form_state->getValue('add_default_roles');
      $add_admin_role = $form_state->getValue('add_admin_role');
      $add_admin_outsider = $form_state->getValue('add_admin_outsider');
      $add_admin_insider = $form_state->getValue('add_admin_insider');

      if ($add_default_roles || $add_admin_role || $add_admin_outsider || $add_admin_insider) {
        $storage = $this->entityTypeManager->getStorage('group_role');
        assert($storage instanceof GroupRoleStorageInterface);

        if ($add_default_roles) {
          $storage->save($storage->create([
            'id' => "$group_type_id-anonymous",
            'label' => $this->t('Anonymous'),
            'weight' => -102,
            'scope' => PermissionScopeInterface::OUTSIDER_ID,
            'global_role' => RoleInterface::ANONYMOUS_ID,
            'group_type' => $group_type_id,
          ]));

          $storage->save($storage->create([
            'id' => "$group_type_id-outsider",
            'label' => $this->t('Outsider'),
            'weight' => -101,
            'scope' => PermissionScopeInterface::OUTSIDER_ID,
            'global_role' => RoleInterface::AUTHENTICATED_ID,
            'group_type' => $group_type_id,
          ]));

          $storage->save($storage->create([
            'id' => "$group_type_id-member",
            'label' => $this->t('Member'),
            'weight' => -100,
            'scope' => PermissionScopeInterface::INSIDER_ID,
            'global_role' => RoleInterface::AUTHENTICATED_ID,
            'group_type' => $group_type_id,
          ]));
        }

        if ($add_admin_role) {
          $storage->save($storage->create([
            'id' => "$group_type_id-admin",
            'label' => $this->t('Admin'),
            'weight' => 100,
            'scope' => PermissionScopeInterface::INDIVIDUAL_ID,
            'group_type' => $group_type_id,
            'admin' => TRUE,
          ]));

          // Optionally auto-assign the admin role to group creators.
          if ($form_state->getValue('assign_admin_role')) {
            $group_type->set('creator_roles', [$group_type_id . '-admin'])->save();
          }
        }

        if ($add_admin_outsider || $add_admin_insider) {
          $base = [
            'label' => $this->t('Administrator'),
            'global_role' => $form_state->getValue('global_admin_role_id'),
            'group_type' => $group_type_id,
            'admin' => TRUE,
          ];

          if ($add_admin_outsider) {
            $storage->save($storage->create([
              'id' => "$group_type_id-admin_out",
              'weight' => 101,
              'scope' => PermissionScopeInterface::OUTSIDER_ID,
            ] + $base));
          }

          if ($add_admin_insider) {
            $storage->save($storage->create([
              'id' => "$group_type_id-admin_in",
              'weight' => 102,
              'scope' => PermissionScopeInterface::INSIDER_ID,
            ] + $base));
          }
        }
      }
    }

    $form_state->setRedirectUrl($group_type->toUrl('collection'));
  }

}
