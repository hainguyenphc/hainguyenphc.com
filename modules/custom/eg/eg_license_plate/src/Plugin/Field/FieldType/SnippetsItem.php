<?php

namespace Drupal\eg_license_plate\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "snippets_code",
 *   label = @Translation("Snippets"),
 *   description = @Translation("It stores code snippets in the db"),
 *   default_widget = "default_snippets_widget",
 *   default_formatter = "default_snippets_formatter"
 * )
 */
class SnippetsItem extends FieldItemBase {

  /**
   * {@inheritDoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['source_code'] = DataDefinition::create('string')
      ->setLabel(t('Snippet code'));
    $properties['source_description'] = DataDefinition::create('string')
      ->setLabel(t('Snippet Description'));
    $properties['source_lang'] = DataDefinition::create('string')
      ->setLabel(t('Sinnpet Language'));
    
    return $properties;
  }

  /**
   * {@inheritDoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'source_code' => [
          'type' => 'text',
          'size' => 'big',
          'not null' => TRUE,
        ],
        'source_description' => [
          'type' => 'varchar',
          'length' => '1024',
          'not null' => FALSE,
        ],
        'source_lang' => [
          'type' => 'varchar',
          'length' => '256',
          'not null' => TRUE,
        ],
      ],
    ];
    
    return $schema;
  }

  public function isEmpty() {
    $source_code = $this->get('source_code')->getValue();

    return $source_code === '' || is_null($source_code);
  }

}
