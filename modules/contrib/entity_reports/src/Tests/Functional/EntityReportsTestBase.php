<?php

namespace Drupal\entity_reports\Tests\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityReportsTestBase.
 *
 * Helper class for testing.
 *
 * @package Drupal\entity_reports\Tests\Functional
 * @group entity_reports
 */
abstract class EntityReportsTestBase extends BrowserTestBase {

  use ContentTypeCreationTrait;

  /**
   * Create sample content type.
   *
   * @param array $values
   *   Values.
   * @param array $fields
   *   Fields.
   *
   * @return \Drupal\node\Entity\NodeType
   *   Node type object.
   *
   * @throws \Exception
   */
  public function createSampleContentType(array $values, array $fields) {
    if (!isset($values['type'])) {
      do {
        $id = strtolower($this->randomMachineName(8));
      } while (NodeType::load($id));
    }
    else {
      $id = $values['type'];
    }
    $values += [
      'type' => $id,
      'name' => $id,
    ];
    $type = NodeType::create($values);
    $status = $type->save();

    // node_add_body_field($type);
    foreach ($fields as $name => $definition) {
      $this->createField($type, $name, $definition);
    }

    if ($this instanceof TestCase) {
      $this->assertSame($status,
        SAVED_NEW,
        (new FormattableMarkup(
          'Created content type %type.',
          ['%type' => $type->id()])
        )->__toString()
      );
    }
    else {
      $this->assertEquals($status, SAVED_NEW, (new FormattableMarkup('Created content type %type.',
        ['%type' => $type->id()])
      )->__toString());
    }

    return $type;
  }

  /**
   * Create field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $type
   *   Type.
   * @param string $name
   *   Name.
   * @param string $definition
   *   Definition.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\field\Entity\FieldConfig
   *   Entity inteface or field configuration.
   *
   * @throws \Exception
   */
  public function createField(EntityInterface $type, $name, $definition) {
    // Add or remove the body field, as needed.
    $field_storage = FieldStorageConfig::loadByName('node', $name);
    $field = FieldConfig::loadByName('node', $type->id(), $name);
    if (empty($field)) {
      $field = FieldConfig::create($definition + [
        'field_storage' => $field_storage,
        'bundle' => $type->id(),
      ]);
      $field->save();
    }
    return $field;
  }

}
