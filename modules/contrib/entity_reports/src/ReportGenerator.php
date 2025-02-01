<?php

namespace Drupal\entity_reports;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ReportGenerator contains commonly shared utilities.
 *
 * @package Drupal\entity_reports
 */
class ReportGenerator {

  use StringTranslationTrait;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ReportGenerator constructor.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    EntityFieldManagerInterface $entityFieldManager,
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    LanguageManagerInterface $languageManager,
    ConfigFactoryInterface $configFactory
  ) {
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->languageManager = $languageManager;
    $this->configFactory = $configFactory;
  }

  /**
   * Returns the entity structure for a given entity type.
   *
   * @param string $entity_type
   *   Entity type, i.e. node, taxonomy_term.
   *
   * @return array[]
   *   An associative array keyed by bundle machine name.
   *
   * @throws \Exception
   */
  public function getEntityTypeStructure($entity_type) {
    $structure = [];
    $bundle_info = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
    foreach ($bundle_info as $bundle => $info) {
      $structure[$bundle] = [
        'label' => $info['label'],
        'fields' => $this->generateEntityFieldsReport($entity_type, $bundle),
        'statistics' => $this->generateEntityStatisticsReport($entity_type, $bundle),
      ];
    }
    // Sort by label.
    $this->sortArrayByLabelKey($structure);
    return $structure;
  }

  /**
   * Extract field information about an entity.
   *
   * @param string $entity_type
   *   Entity type, i.e. node, taxonomy_term.
   * @param string $bundle
   *   Entity bundle, i.e. page, article.
   *
   * @return array
   *   Array with field information.
   *
   * @throws \Exception
   */
  public function generateEntityFieldsReport($entity_type, $bundle) {
    $ret = [];
    $base_fields = $this->entityFieldManager->getBaseFieldDefinitions($entity_type);
    $fields_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    foreach ($fields_definitions as $field_definition) {
      /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */
      if (empty($field_definition->getTargetBundle())) {
        continue;
      }
      if (in_array($field_definition->getName(), array_keys($base_fields))) {
        continue;
      }
      $field_name = $field_definition->getName();
      $ret[$field_name] = $this->fieldData($field_name, $field_definition);
      $columns = $field_definition->getFieldStorageDefinition()->getColumns();
      if (count($columns) > 1) {
        foreach ($columns as $column_name => $column_info) {
          $ret["$field_name.$column_name"] = $this->subFieldData($field_name, $field_definition, $column_name, $column_info);
        }
      }
    }
    // Sort by label.
    $this->sortArrayByLabelKey($ret);
    return $ret;
  }

  /**
   * Generate data for a single field.
   *
   * @param string $field_name
   *   The name of the field.
   * @param Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition object.
   *
   * @return array
   *   An array of data with the following keys:
   *   - label (string)
   *   - machine_name (string)
   *   - description (string)
   *   - type (string)
   *   - required (bool)
   *   - required_human (string)
   *   - translatable (bool)
   *   - translatable_human (string)
   *   - target (string)
   *   - cardinality (int)
   *   - cardinality_human (string)
   */
  protected function fieldData($field_name, FieldDefinitionInterface $field_definition) {
    $count = $field_definition->getFieldStorageDefinition()->getCardinality();
    $cardinality = $count;
    if ($count == -1) {
      $cardinality_human = $this->t('Unlimited values');
    }
    else {
      $cardinality_human = $this->formatPlural($count, 'One value', '@count values', ['@count' => $count]);
    }
    $field_type = $field_definition->getType();
    if ($field_definition->getType() == 'entity_reference') {
      $field_type .= ' (' . $field_definition->getSetting('target_type') . ')';
    }
    $required = $field_definition->isRequired();
    $required_human = ($required ? $this->t('True') : $this->t('False'));
    $translatable = $field_definition->isTranslatable();
    $translatable_human = ($translatable ? $this->t('True') : $this->t('False'));
    $target = $field_definition->getSetting('handler_settings');
    $target_bundles = [];
    if (!empty($target['target_bundles'])) {
      $target_bundles = self::getBundleNames($target['target_bundles'], $field_definition->getSetting('target_type'));
    }
    return [
      'label' => $field_definition->getLabel(),
      'machine_name' => $field_name,
      'description' => $field_definition->getDescription(),
      'type' => $field_type,
      'required' => $required,
      'required_human' => $required_human,
      'translatable' => $translatable,
      'translatable_human' => $translatable_human,
      'target' => implode(', ', $target_bundles),
      'cardinality' => $cardinality,
      'cardinality_human' => $cardinality_human,
    ];
  }

  /**
   * Generate data for a single field and column.
   *
   * @param string $field_name
   *   The name of the field.
   * @param Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition object.
   * @param string $column_name
   *   The name of a column or subfield.
   * @param array $column_info
   *   Information about the individual database column. The following keys are
   *   used:
   *   - description
   *   - type.
   *
   * @return array
   *   An array of data with the same keys as fieldData(), but only the
   *   following are non-empty:
   *   - label (string)
   *   - machine_name (string)
   *   - description (string)
   *   - type (string)
   */
  protected function subFieldData($field_name, FieldDefinitionInterface $field_definition, $column_name, array $column_info) {
    $column_info += ['description' => '', 'type' => ''];
    return [
      'label' => $field_definition->getLabel(),
      'machine_name' => "$field_name.$column_name",
      'description' => $column_info['description'],
      'type' => $column_info['type'],
      'required' => FALSE,
      'required_human' => '',
      'translatable' => FALSE,
      'translatable_human' => '',
      'target' => '',
      'cardinality' => '',
      'cardinality_human' => '',
    ];
  }

  /**
   * Extract bundle names for multiple machine names.
   *
   * @param array $machine_names
   *   Array of machine names.
   * @param string $entity_type
   *   Name of the entity type.
   *
   * @return array
   *   Array of human bundle names.
   */
  public function getBundleNames(array $machine_names, $entity_type) {
    $ret = [];
    foreach ($machine_names as $machine_name) {
      $ret[$machine_name] = $machine_name;
      if (($bundle_name = $this->getBundleName($machine_name, $entity_type)) && $bundle_name != $machine_name) {
        $ret[$machine_name] = sprintf('%s (%s)', $bundle_name, $machine_name);
      }
    }
    return $ret;
  }

  /**
   * Extract bundle name for a single machine name.
   *
   * @param string $machine_name
   *   Name of machine name.
   * @param string $entity_type
   *   Name of the entity type.
   *
   * @return mixed
   *   Human bundle name.
   */
  public function getBundleName($machine_name, $entity_type) {
    $all_bundles = drupal_static(__METHOD__);
    if (empty($all_bundles)) {
      $all_bundles = $this->entityTypeBundleInfo->getAllBundleInfo();
    }
    if (!empty($all_bundles[$entity_type][$machine_name]['label'])) {
      return $all_bundles[$entity_type][$machine_name]['label'];
    }
    return $machine_name;
  }

  /**
   * Generate statistics about an entity.
   *
   * @param string $entity_type
   *   Entity type, i.e. node, taxonomy_term.
   * @param string $bundle
   *   Entity bundle, i.e. page, article.
   *
   * @return array
   *   Array with field information.
   *
   * @throws \Exception
   */
  public function generateEntityStatisticsReport($entity_type, $bundle) {
    // Gets entity types that are reportable.
    $entity_types = (array) $this->configFactory
      ->get('entity_reports.settings')
      ->get('reported_entity_types');
    if (empty($entity_types)) {
      foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $et) {
        if ($et->entityClassImplements(FieldableEntityInterface::class)) {
          $entity_types[$entity_type_id] = $et->getLabel();
        }
      }
    }

    $bundle_info = $this->entityTypeBundleInfo->getAllBundleInfo();
    $bundle_info = array_intersect_key($bundle_info, $entity_types);
    $info = $bundle_info[$entity_type][$bundle];

    $data[$entity_type] = [
      'label' => $entity_types[$entity_type],
      'bundles' => [],
    ];

    // Gets total number of instances.
    $query = $this->entityTypeManager->getStorage($entity_type)->getQuery();
    $all_bundle_fields = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    // NOTE: Some entity types do not use bundles.
    $bundle_key = $this->entityTypeManager->getDefinition($entity_type)
      ->getKey('bundle');
    if (isset($all_bundle_fields[$bundle_key])) {
      $query->condition($bundle_key, $bundle);
    }

    $row = [
      'label' => $info['label'],
      'id' => $bundle,
      'translatable' => array_key_exists('translatable', $info) && $info['translatable'] ? $this->t('True') : $this->t('False'),
      'count' => $query->accessCheck(FALSE)->count()->execute(),
    ];

    // Process each language.
    $languages = [
      LanguageInterface::LANGCODE_NOT_SPECIFIED => $this->languageManager->getLanguage(LanguageInterface::LANGCODE_NOT_SPECIFIED),
    ] + $this->languageManager->getLanguages();
    foreach ($languages as $langId => $language) {
      // NOTE: Some entity types do not use language codes.
      if (isset($all_bundle_fields['langcode'])) {
        $language_query = clone $query;
        $language_query->condition('langcode', $langId);
        $row[$langId] = $language_query->count()->execute();
      }
      else {
        $row[$langId] = 'N/A';
      }
    }

    return $row;
  }

  /**
   * Helper function to sort an array by 'label' keys.
   *
   * @param array $array
   *   Array to be sorted.
   */
  private function sortArrayByLabelKey(array &$array) {
    uasort($array, function ($a, $b) {
      return strcasecmp($a['label'], $b['label']);
    });
  }

}
