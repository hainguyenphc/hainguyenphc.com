<?php

namespace Drupal\entity_reports_csv\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\entity_reports\Event\EntityReportsExportFormats;

/**
 * Class EntityReportsCsvFormatsSubscriber.
 *
 * Subscriber for report generation.
 *
 * @package Drupal\entity_reports_csv\EventSubscriber
 */
class EntityReportsCsvFormatsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      EntityReportsExportFormats::EVENT_NAME => 'onExportFormats',
    ];
  }

  /**
   * Subscribe to the `entity_reports_export_formats` event dispatched.
   *
   * @param \Drupal\entity_reports\Event\EntityReportsExportFormats $event
   *   Event object.
   */
  public function onExportFormats(EntityReportsExportFormats $event) {
    $event->exportFormats['csv'] = 'CSV';
  }

}
