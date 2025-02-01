<?php

namespace Drupal\entity_reports\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\SortArray;

/**
 * Entity reports settings.
 */
class EntityReportsSettingsForm extends ConfigFormBase {

  const CONFIG_ID = 'entity_reports.settings';

  /**
   * The Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The router builder.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * The Cache Render.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheRender;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, RouteBuilderInterface $router_builder, CacheBackendInterface $cache_render) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->routerBuilder = $router_builder;
    $this->cacheRender = $cache_render;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('router.builder'),
      $container->get('cache.render')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::CONFIG_ID];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_reports_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_ID);

    $available_entity_types = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->entityClassImplements(FieldableEntityInterface::class)) {
        $available_entity_types[$entity_type_id] = $entity_type->getLabel();
      }
    }
    asort($available_entity_types);
    $form['reported_entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Reported entity types'),
      '#description' => $this->t('When all available options are unchecked, all fieldable entity types are reported.'),
      '#options' => $available_entity_types,
      '#default_value' => !empty($config->get('reported_entity_types')) ? $config->get('reported_entity_types') : [],
    ];

    $group_class = 'report-fields-order-weight';
    $form['report_fields'] = [
      '#type' => 'table',
      '#caption' => $this->t('<h4>Report Fields</h4>'),
      '#header' => [
        $this->t('Label'),
        $this->t('Machine Name'),
        $this->t('Weight'),
        $this->t('Status'),
      ],
      '#empty' => $this->t('No items.'),
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ],
      ],
    ];

    // Build rows.
    $items = $config->get('report_fields');
    foreach ($items as $value) {
      $form['report_fields'][$value['machine_name']]['#attributes']['class'][] = 'draggable';
      $form['report_fields'][$value['machine_name']]['#weight'] = $value['weight'];

      // Label column.
      $form['report_fields'][$value['machine_name']]['label_human'] = [
        '#plain_text' => $value['label'],
      ];

      // Machine Name column.
      $form['report_fields'][$value['machine_name']]['machine_name_human'] = [
        '#plain_text' => $value['machine_name'],
      ];

      // Weight column.
      $form['report_fields'][$value['machine_name']]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $value['label']]),
        '#title_display' => 'invisible',
        '#default_value' => $value['weight'],
        '#attributes' => ['class' => [$group_class]],
      ];

      // Status column.
      $form['report_fields'][$value['machine_name']]['status'] = [
        '#type' => 'checkbox',
        '#default_value' => $value['status'],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_ID);

    // Processes `report_fields` data.
    $prev_report_fields = $config->get('report_fields');
    $report_fields_input = $form_state->getValue('report_fields');
    $report_fields = $this->processWeightAndStatusData($prev_report_fields, $report_fields_input);

    $config
      ->set('reported_entity_types', array_values(array_filter($form_state->getValue('reported_entity_types'))))
      ->set('report_fields', $report_fields)
      ->save();

    $this->routerBuilder->rebuild();
    $this->cacheRender->invalidateAll();

    parent::submitForm($form, $form_state);
  }

  /**
   * Helper functions to process form data for values with status and weight.
   *
   * @param array $config_data
   *   Current configuration data.
   * @param array $input_data
   *   New data input via settings form.
   *
   * @return array
   *   Processed data ready for save.
   */
  protected function processWeightAndStatusData(array $config_data, array $input_data) {
    $processed_data = [];

    // Assigns new data input via settings form.
    foreach ($config_data as $field) {
      $machine_name = $field['machine_name'];
      $processed_data[] = [
        'label' => $field['label'],
        'machine_name' => $machine_name,
        'weight' => $input_data[$machine_name]['weight'],
        'status' => $input_data[$machine_name]['status'],
      ];
    }

    // Sorts fields in the expected order by weight.
    uasort($processed_data, [SortArray::class, 'sortByWeightElement']);

    return $processed_data;
  }

}
