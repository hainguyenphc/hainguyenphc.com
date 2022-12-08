<?php

namespace Drupal\eg_sports\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

class TeamForm extends ContentEntityForm {

  public function save(array $form, FormStateInterface $form_state) {
    $team = $this->entity;

    $status = parent::save($form, $form_state);

    $form_state->setRedirect(
      'entity.team.canonical',
      [
        'team' => $team->id(),
      ]
    );
  }

}
