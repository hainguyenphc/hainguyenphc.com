<?php

namespace Drupal\eg_license_plate\Plugin\Field\FieldWidget;

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

    return $elements;
  }
  
}
