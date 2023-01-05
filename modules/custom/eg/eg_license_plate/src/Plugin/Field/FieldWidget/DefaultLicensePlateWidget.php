<?php

namespace Drupal\eg_license_plate\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\eg_license_plate\Plugin\Field\FieldType\LicensePlateItem;

/**
 * @FieldWidget(
 *   id = "default_license_plate_widget",
 *   label = @Translation("Default license plate widget"),
 *   field_types = {
 *      "license_plate"
 *   }
 * )
 */
class DefaultLicensePlateWidget extends WidgetBase {

  use StringTranslationTrait;

  public static function defaultSettings() {
    return [
      'number_size' => 255, // 255 numbers at max.
      'code_size' => 5, // 5 characters at max.
      'fieldset_state' => 'open',
      'placeholder' => [
        'number' => '',
        'code' => '',
      ],
    ] + parent::defaultSettings();
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['number_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of plate number textfield.'),
      '#default_value' => $this->getSetting('number_size'),
      '#required' => TRUE,
      '#min' => 1,
      '#max' => $this->getFieldSetting(LicensePlateItem::NUMBER_MAX_LENGTH),
    ];

    $elements['code_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of plate code textfield.'),
      '#default_value' => $this->getSetting('code_size'),
      '#required' => TRUE,
      '#min' => 1,
      '#max' => $this->getFieldSetting(LicensePlateItem::CODE_MAX_LENGTH),
    ];

    $elements['fieldset_state'] = [
      '#type' => 'select',
      '#title' => $this->t('Fieldset default state'),
      '#options' => [
        'open' => $this->t('Open'),
        'closed' => $this->t('Closed'),
      ],
      '#default_value' => $this->getSetting('fieldset_state'),
      '#description' => $this->t('The default state of the fieldset containing 2 plate fields: open or closed'),
    ];

    $elements['placeholder'] = [
      '#type' => 'details',
      '#title' => $this->t('Placeholder'),
      '#description' => $this->t('Text shown inside the text field until a value is entered.'),
    ];

    $placeholder_settings = $this->getSetting('placeholder');

    $elements['placeholder']['number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number field'),
      '#default_value' => $placeholder_settings['number'],
    ];

    $elements['placeholder']['code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Code field'),
      '#default_value' => $placeholder_settings['code'],
    ];

    return $elements;
  }

  /**
   * Summary displayed on the "Manage Form Display" page.
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('License plate size: @number (for number) and @code (for code)', [
      '@number' => $this->getSetting('number_size'), 
      '@code' => $this->getSetting('code_size'),
    ]);

    $placeholder_settings = $this->getSetting('placeholder');
    if (!empty($placeholder_settings['number']) && !empty($placeholder_settings['code'])) {
      $placeholder = $placeholder_settings['number'] . ' ' . $placeholder_settings['code'];
      $summary[] = $this->t('Placeholder: @placeholder', [
        '@placeholder' => $placeholder,
      ]);
    }

    $summary[] = $this->t('Fieldset state: @state', [
      '@state' => $this->getSetting('fieldset_state'),
    ]);

    return $summary;
  }

  /**
   * {@inheritDoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    
  }
  
}
