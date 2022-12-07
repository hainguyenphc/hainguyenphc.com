<?php

namespace Drupal\eg_sports\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

class TeamTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\eg_sports\Entity\TeamTypeInterface */
    $team_type = $this->entity;

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $team_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\eg_sports\Entity\TeamType::load'
      ],
      // if the type is existing, disables the form field.
      '#disabled' => !($team_type->isNew()),
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $team_type->label(),
      '#description' => $this->t('What would this team type called?'),
      '#required' => TRUE,
    ];

    $form['indoors'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('It plays indoors?'),
      '#default_value' => $team_type->isSinglePlayer(),
      '#description' => 'If checked, this team type has only one player.',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\eg_sports\Entity\TeamTypeInterface */
    $team_type = $this->entity;

    $status = $team_type->save();

    /** @var \Drupal\Core\Url */
    $collection_url = $team_type->toUrl('collection');

    $form_state->setRedirectUrl($collection_url);
  }

}
