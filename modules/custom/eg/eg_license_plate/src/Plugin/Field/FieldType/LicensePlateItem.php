<?php

namespace Drupal\eg_license_plate\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "license_plate",
 *   label = @Translation("License Plate"),
 *   description = @Translation("Field for storing license plates"),
 *   default_widget = "default_license_plate_widget",
 *   default_formatter = "default_license_plate_formatter"
 * )
 * 
 * See web/modules/custom/eg_license_plate/config/schema/license_plate.schema.yml file.
 * The pattern is field.storage_settings.FIELD_TYPE_PLUGIN_ID
 * The pattern is field.field_settings.
 */
class LicensePlateItem extends FieldItemBase {

  use StringTranslationTrait;

  public const NUMBER_MAX_LENGTH = 'number_max_length';
  public const CODE_MAX_LENGTH = 'code_max_length';

  /**
   * {@inheritDoc}
   */
  public static function defaultStorageSettings() {
    // Defines custom storage settings the field has.
    // Provides defaults for them.
    // Storage settings are configs that apply everywhere the field is used.
    return [
      self::NUMBER_MAX_LENGTH => 255,
      self::CODE_MAX_LENGTH => 5,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritDoc}
   * 
   * @see admin/structure/types/manage/car/fields/node.car.field_license_plate/storage page.
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];

    $elements[self::NUMBER_MAX_LENGTH] = [
      '#type' => 'number',
      '#title' => $this->t('Plate number maximum length'),
      '#default_value' => $this->getSetting(self::NUMBER_MAX_LENGTH),
      '#required' => TRUE,
      '#description' => $this->t('Maximum length for the plate number in characters.'),
      '#min' => 2,
      '#disabled' => $has_data,
    ];

    $elements[self::CODE_MAX_LENGTH] = [
      '#type' => 'number',
      '#title' => $this->t('Plate code maximum length'),
      '#default_value' => $this->getSetting(self::CODE_MAX_LENGTH),
      '#required' => TRUE,
      '#description' => $this->t('Maximum length for the plate code in characters.'),
      '#min' => 2,
      '#disabled' => $has_data,
    ];

    return $elements + parent::storageSettingsForm($form, $form_state, $has_data);
  }

  /**
   * {@inheritDoc}
   * 
   * Field settings are specific to each field instance on a certain entity bundle.
   * For example:
   *  - field settings for CT 1
   *  - field settings for CT 2
   *  - etc.
   */
  public static function defaultFieldSettings() {
    return [
      'code' => '',
    ] + parent::defaultFieldSettings();
  }

  /** 
   * {@inheritDoc}
   * @see /admin/structure/types/manage/car/fields/node.car.field_license_plate/storage page.
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['codes'] = [
      '#title' => $this->t('Available license plate codes'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('codes'),
      '#description' => $this->t('If you want the field to have a select list with some available license plate codes instead of a textfield, please provide the available codes. Each one is on a new line.'),
    ];

    return $element;
  }

  /**
   * {@inheritDoc}
   * 
   * @see node__field_license_plate table.
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'number' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting(self::NUMBER_MAX_LENGTH),
        ],
        'code' => [
          'type' => 'varchar',
          'length' => (int) $field_definition->getSetting(self::CODE_MAX_LENGTH),
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritDoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['number'] = DataDefinition::create('string')->setLabel(t('Plate number'));
    $properties['code'] = DataDefinition::create('string')->setLabel(t('Plate code'));

    return $properties;
  }

  /**
   * {@inheritDoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    $manager = \Drupal::typedDataManager()->getValidationConstraintManager();

    $number_max_length = $this->getSetting(self::NUMBER_MAX_LENGTH);
    $code_max_length = $this->getSetting(self::CODE_MAX_LENGTH);

    $constraints[] = $manager->create('ComplexData', [
      'number' => [
        'Length' => [
          'max' => $number_max_length,
          'maxMessage' => $this->t('%name may not be longer than @max characters.', [
            '%name' => $this->getFieldDefinition()->getLabel(),
            '@max' => $number_max_length,
          ]),
        ],
      ], // number
      'code' => [
        'Length' => [
          'max' => $code_max_length,
          'maxMessage' => $this->t('%name may not be longer than @max characters.', [
            '%name' => $this->getFieldDefinition()->getLabel(),
            '@max' => $code_max_length,
          ]),
        ],
      ], // code
    ]);

    return $constraints;
  }

  /**
   * {@inheritDoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    
    $values['number'] = $random->word(mt_rand(1, $field_definition->getSetting(self::NUMBER_MAX_LENGTH)));
    $values['code'] = $random->word(mt_rand(1, $field_definition->getSetting(self::CODE_MAX_LENGTH)));

    return $values;
  }

  /**
   * {@inheritDoc}
   */
  public function isEmpty() {
    $number = $this->get('number')->getValue();
    $code = $this->get('code')->getValue();

    return is_null($number) || empty($number) || is_null($code) || empty($code);
  }

}
