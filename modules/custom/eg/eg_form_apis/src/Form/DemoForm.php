<?php

namespace Drupal\eg_form_apis\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class DemoForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'eg_form_apis.demo_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['color'] = [
      '#type' => 'radios',
      '#title' => $this->t('Pick a color'),
      '#options' => [
        'blue' => $this->t('Blue'),
        'white' => $this->t('White'),
        'black' => $this->t('Black'),
        'other' => $this->t('Other, please specify'),
      ],
    ];

    $form ['custom_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your custom color'),
      '#states' => [
        'visible' => [
          ':input[name="color"]' => [
            '!value' => 'other',
          ],
        ],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  function submitForm(array &$form, FormStateInterface $form_state) {
    
  }

}