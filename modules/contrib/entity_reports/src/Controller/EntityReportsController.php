<?php

namespace Drupal\entity_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\entity_reports\ReportGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\entity_reports\Event\EntityReportsExportFormats;
use Drupal\entity_reports\Event\EntityReportsExportProcessors;

/**
 * Class EntityReportsController handle routes for entity reports.
 */
class EntityReportsController extends ControllerBase {

  /**
   * The report generator service.
   *
   * @var \Drupal\entity_reports\ReportGenerator
   */
  protected $generator;

  /**
   * List of report fields.
   *
   * @var array
   */
  protected $reportFields;

  /**
   * The event dipatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @noinspection PhpParamsInspection */
    return new static(
      $container->get('entity_reports.generator'),
      $container->get('event_dispatcher'),
      $container->get('language_manager')
    );
  }

  /**
   * EntityReportsController constructor.
   *
   * @param \Drupal\entity_reports\ReportGenerator $reportGenerator
   *   The report generator service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(
    ReportGenerator $reportGenerator,
    EventDispatcherInterface $eventDispatcher,
    LanguageManagerInterface $languageManager) {
    $this->generator = $reportGenerator;
    $config = $this->config('entity_reports.settings')->get('report_fields');
    // Generates list of currently enabled report fields.
    $report_fields = [];
    foreach ($config as $field) {
      if ($field['status']) {
        $report_fields[$field['machine_name']] = $this->t('@label', ['@label' => $field['label']]);
      }
    }
    $this->reportFields = $report_fields;
    $this->eventDispatcher = $eventDispatcher;
    $this->languageManager = $languageManager;
  }

  /**
   * Returns the title for an Entity report page.
   *
   * @param string $entity_type
   *   Entity type, i.e. node, taxonomy_term.
   *
   * @return string
   *   The report page title.
   *
   * @throws \Exception
   */
  public function displayReportTitle($entity_type) {
    return $this->t('Entity reports: @entity_type_label', [
      '@entity_type_label' => $this->entityTypeManager()->getDefinition($entity_type)->getLabel(),
    ]);
  }

  /**
   * Displays a report for a given entity type.
   *
   * @param string $entity_type
   *   Entity type, i.e. node, taxonomy_term.
   *
   * @return array
   *   A renderable array as expected by the renderer service.
   *
   * @throws \Exception
   */
  public function displayReport($entity_type) {
    $definition = $this->entityTypeManager()->getDefinition($entity_type);
    $build = [];
    $structure = $this->generator->getEntityTypeStructure($entity_type);
    foreach ($structure as $bundle => $data) {
      $rows = [];
      foreach ($data['fields'] as $field) {
        $row = [];
        foreach (array_keys($this->reportFields) as $machine_name) {
          $row[$machine_name] = (!empty($field[$machine_name . '_human'])) ? $field[$machine_name . '_human'] : $field[$machine_name];
        }
        $rows[] = $row;
      }
      // Builds report export links.
      $event = new EntityReportsExportFormats();
      $this->eventDispatcher->dispatch($event, EntityReportsExportFormats::EVENT_NAME);
      $export_formats = $event->exportFormats;
      $export_links = [];
      foreach ($export_formats as $format_machine_name => $format_label) {
        $export_links[] = Link::fromTextAndUrl(
          $this->t('%text', ['%text' => $format_label]),
          Url::fromRoute("entity_reports.entity_structure.$entity_type.$format_machine_name")
        )->toString();
      }
      $build['info'] = [
        '#markup' => $this->t('Open each @bundle_label below to see details about its fields. You can also download this report in the following formats:', [
          '@bundle_label' => $definition->getBundleLabel(),
        ]) . ' ' . implode(', ', $export_links),
      ];
      $statistics_headers = [
        $this->t('Label'),
        $this->t('Machine name'),
        $this->t('Translatable'),
        $this->t('# entities'),
      ];
      $languages = [
        LanguageInterface::LANGCODE_NOT_SPECIFIED => $this->languageManager->getLanguage(LanguageInterface::LANGCODE_NOT_SPECIFIED),
      ] + $this->languageManager->getLanguages();
      foreach ($languages as $langId => $language) {
        $statistics_headers[$langId] = $language->getName();
      }

      $build[$bundle . '_wrapper'] = [
        '#title' => $data['label'],
        '#type' => 'details',
        '#open' => FALSE,
        'statistics' => [
          '#type' => 'table',
          '#header' => $statistics_headers,
          '#caption' => $this->t('Statistics about this entity'),
          '#rows' => [$data['statistics']],
          '#empty' => $this->t('No statistics available.'),
        ],
        'fields' => [
          '#type' => 'table',
          '#header' => $this->reportFields,
          '#caption' => $this->t('Structure of this entity type'),
          '#rows' => $rows,
          '#empty' => $this->t('No fields configured.'),
        ],
      ];
    }

    return $build;
  }

  /**
   * Lists available entity type reports.
   *
   * @return array
   *   A renderable array as expected by the renderer service.
   */
  public function availableEntityTypes() {
    $build = [];

    // Builds report export links.
    $event = new EntityReportsExportFormats();
    $this->eventDispatcher->dispatch($event, EntityReportsExportFormats::EVENT_NAME);
    $export_formats = $event->exportFormats;
    $export_links = [];
    foreach ($export_formats as $format_machine_name => $format_label) {
      $export_links[$format_machine_name] = Link::fromTextAndUrl(
        $this->t('@text', ['@text' => $format_label]),
        Url::fromRoute("entity_reports.statistics.$format_machine_name")
      )->toString();
    }
    ksort($export_links);
    $build['statistics'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Statistics export links'),
    ];
    $build['statistics']['export_links'] = [
      '#theme' => 'item_list',
      '#items' => $export_links,
    ];

    $build['individual_reports_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Structure report'),
    ];
    $items = [];
    $configured_types = $this->config('entity_reports.settings')
      ->get('reported_entity_types');
    foreach ($this->entityTypeManager()
      ->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->entityClassImplements(FieldableEntityInterface::class)) {
        if (!empty($configured_types) && !in_array($entity_type_id, $configured_types, TRUE)) {
          continue;
        }
        $url = Url::fromRoute("entity_reports.entity_structure.$entity_type_id");
        $items[(string) $entity_type->getLabel()] = Link::fromTextAndUrl($entity_type->getLabel(), $url)
          ->toString();
      }
    }
    ksort($items);
    $build['individual_reports_fieldset']['entity_report_list'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    return $build;
  }

  /**
   * Displays a report for a given entity type.
   *
   * @param string $entity_type
   *   Entity type, i.e. node, taxonomy_term.
   * @param string $export_format
   *   The export format, i.e. json, xml.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   *
   * @throws \Exception
   */
  public function exportReport($entity_type, $export_format) {
    $structure = $this->generator->getEntityTypeStructure($entity_type);
    return $this->export(['bundles' => $structure], $entity_type, $export_format);
  }

  /**
   * Provides the content in a specified format.
   *
   * @param array $content
   *   The content of response.
   * @param string $entity_type
   *   The entity type of the exported data.
   * @param string $type
   *   The content type of response. Optional, defaults to 'json'.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response object.
   */
  public function export(array $content, $entity_type, $type = 'json') {
    $response = new Response();
    $response->headers->set('Content-Type', 'application/' . $type);

    // Checks for non-default export formats.
    if (!array_key_exists($type, EntityReportsExportFormats::DEFAULT_EXPORT_FORMATS)) {
      $event = new EntityReportsExportProcessors($content, $entity_type, $this->reportFields, $type);
      $this->eventDispatcher->dispatch($event, EntityReportsExportProcessors::EVENT_NAME);
      $content = $event->content;
      foreach ($event->responseHeaders as $key => $value) {
        $response->headers->set($key, $value);
      }
    }
    elseif ($type == 'xml') {
      if (count($content) == 1) {
        $rootElement = key($content);
        $content = reset($content);
      }
      else {
        $rootElement = 'root';
      }
      $xml = new \SimpleXMLElement("<{$rootElement}/>");
      $this->arrayToXml($content, $xml, $rootElement);
      $content = $xml->asXML();
    }
    else {
      // Defaults to JSON format.
      $content = json_encode($content);
    }

    $response->setContent($content);
    return $response;
  }

  /**
   * Returns the title for the statistics report page.
   *
   * @return string
   *   The report page title.
   */
  public function statisticsReportTitle() {
    return $this->t('Entity reports: statistics');
  }

  /**
   * Exports the entity statistics in a specified format.
   *
   * @param string $type
   *   The export format, e.g. json or xml.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   *
   * @throws \Exception
   */
  public function exportStatisticsReport($type) {
    $statistics = [];
    $configured_types = $this->config('entity_reports.settings')
      ->get('reported_entity_types');
    foreach ($this->entityTypeManager()
      ->getDefinitions() as $entity_type_id => $entity_type) {
      if (!$entity_type->entityClassImplements(FieldableEntityInterface::class)) {
        continue;
      }
      if (!empty($configured_types) && !in_array($entity_type_id, $configured_types, TRUE)) {
        continue;
      }

      foreach ($this->generator->getEntityTypeStructure($entity_type_id) as $bundle => $bundle_data) {
        $statistics[$entity_type_id][$bundle] = $bundle_data['statistics'];
      }
    }

    $content = ['bundles' => [], 'entity_types' => $statistics];
    return $this->export($content, $entity_type_id, $type);
  }

  /**
   * Adds an array to an XML element.
   *
   * @param array $array
   *   The input array.
   * @param \SimpleXMLElement $xml
   *   The output XML element.
   * @param string $parentKey
   *   The root element name.
   */
  protected function arrayToXml(array $array, \SimpleXMLElement &$xml, $parentKey) {
    foreach ($array as $key => $value) {
      $xmlKey = !is_numeric($key) ? $key : "item$key";
      $attributes = [];
      switch ($parentKey) {
        case 'contentTypes':
          $xmlKey = 'contentType';
          $attributes['id'] = $key;
          break;

        case 'vocabularies':
          $xmlKey = 'vocabulary';
          $attributes['id'] = $key;
          break;

        case 'fields':
          $xmlKey = 'field';
          $attributes['id'] = $key;
          break;

        case 'terms':
          $xmlKey = 'term';
          $attributes['id'] = $key;
          break;
      }
      if (is_array($value)) {
        $subnode = $xml->addChild($xmlKey);
        $this->arrayToXml($value, $subnode, $xmlKey);
      }
      else {
        $subnode = $xml->addChild($xmlKey, $value);
      }
      foreach ($attributes as $attrKey => $attrValue) {
        $subnode->addAttribute($attrKey, $attrValue);
      }
    }
  }

}
