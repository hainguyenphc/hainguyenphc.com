<?php

namespace Drupal\eg_products\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;

class ProductForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage(
          $this->t('Created the %label product.', ['%label' => $entity->label()]),
          MessengerInterface::TYPE_STATUS,
          FALSE
        );
        break;
      default:
        $this->messenger()->addMessage(
          $this->t('Saved the %label product.', ['%label' => $entity->label()]),
          MessengerInterface::TYPE_STATUS,
          FALSE
        );
        break;
    }

    $form_state->setRedirect(
      'entity.product.canonical',
      ['product' => $entity->id()]
    );
  }

}
