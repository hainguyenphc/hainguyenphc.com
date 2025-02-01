<?php

namespace Drupal\entity_reports_csv\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\entity_reports\Event\EntityReportsExportProcessors;

/**
 * Class EntityReportsCsvProcessorsSubscriber.
 *
 * Subscriber for report processing.
 *
 * @package Drupal\entity_reports_csv\EventSubscriber
 */
class EntityReportsCsvProcessorsSubscriber implements EventSubscriberInterface {

  /**
   * Serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * Constructs the object.
   *
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   Serializer.
   */
  public function __construct(SerializerInterface $serializer) {
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      EntityReportsExportProcessors::EVENT_NAME => 'onExportProcessors',
    ];
  }

  /**
   * Subscribe to the `entity_reports_export_processors` event dispatched.
   *
   * @param \Drupal\entity_reports\Event\EntityReportsExportProcessors $event
   *   Event object.
   */
  public function onExportProcessors(EntityReportsExportProcessors $event) {
    // Sets export values on $event object.
    // CSV Serialization module is required.
    if ($this->serializer->supportsEncoding('csv')) {
      $event->content = $this->arrayToCsv($event);
      // Uses recommended MIME type for CSV.
      // @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Complete_list_of_MIME_types
      $event->responseHeaders['Content-Type'] = 'text/' . $event->type;
    }
    else {
      // Recommends installing the CSV Serialization module.
      $event->content = [
        '#markup' => $this->t('To allow CSV exports of Entity Reports, the <a href="https://www.drupal.org/project/csv_serialization" title="CSV Serialization">CSV Serialization</a> module must be enabled.'),
      ];
    }
  }

  /**
   * Helper function to serialize array data into CSV format.
   *
   * @param \Drupal\entity_reports\Event\EntityReportsExportProcessors $event
   *   Event object.
   *
   * @return string
   *   CSV-formatted string.
   */
  protected function arrayToCsv(EntityReportsExportProcessors $event) {
    $data = [];

    foreach ($event->content['bundles'] as $bundle_machine_name => $bundle) {
      if (!empty($bundle['fields'])) {
        foreach ($bundle['fields'] as $field_machine_name => $field) {
          $row = [
            'entity_type' => $event->entityType,
            'bundle_label' => $bundle['label'],
            'bundle_machine_name' => $bundle_machine_name,
          ];
          foreach (array_keys($event->reportFields) as $field_machine_name) {
            $row['field_' . $field_machine_name] = $field[$field_machine_name];
          }
          $data[] = $row;
        }
      }
    }

    if (empty($event->content['bundles']) && !empty($event->content['entity_types'])) {
      foreach ($event->content['entity_types'] as $entity_type_id => $structure) {
        foreach ($structure as $bundle => $bundle_data) {
          $data[] = [
            'entity_type' => $entity_type_id,
            'bundle' => $bundle,
          ] + $bundle_data;
        }
      }
    }

    return $this->serializer->serialize($data, 'csv');
  }

}
