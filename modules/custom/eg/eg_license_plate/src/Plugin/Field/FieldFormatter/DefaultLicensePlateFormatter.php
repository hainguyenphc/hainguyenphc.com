<?php

namespace Drupal\eg_license_plate\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * @FieldFormatter(
 *    id = "default_license_plate_formatter",
 *    label = @Translation("Default license plate formatter"),
 *    field_types = {
 *      "license_plate"
 *    }
 * )
 * 
 * @see modules/custom/eg/eg_license_plate/config/schema/license_plate.schema.yml file.
 */
class DefaultLicensePlateFormatter extends FormatterBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public static function defaultSettings() {
    return [
      'concatenated' => 1,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritDoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      'concatenated' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Concatenated'),
        '#description' => $this->t('Whether to concatenate code and number into a single string separated by space.'),
        '#default_value' => $this->getSetting('concatenated'),
      ],
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Concatenated: @value', [
      '@value' => (bool) $this->getSetting('concatenated') ? $this->t('Yes') : $this->t('No')
    ]);
    return $summary;
  }

  /**
   * {@inheritDoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->__viewValue($item);
    }
    return $elements;
  }

  /**
   * Generates the output appropriate for one field item.
   */
  protected function __viewValue(FieldItemInterface $item) {
    $code = $item->get('code')->getValue();
    $number = $item->get('number')->getValue();
    return [
      '#theme' => 'license_plate',
      '#code' => $code,
      '#number' => $number,
      '#concatenated' => $this->getSetting('concatenated')
    ];
  }

}