<?php

namespace Drupal\entity_reports\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is fired when Entity Reports is gathering export formats.
 */
class EntityReportsExportFormats extends Event {

  const EVENT_NAME = 'entity_reports_export_formats';

  const DEFAULT_EXPORT_FORMATS = [
    'json' => 'JSON',
    'xml' => 'XML',
  ];

  /**
   * Array of export formats.
   *
   * @var array
   */
  public $exportFormats;

  /**
   * Constructs the object.
   */
  public function __construct() {
    $this->exportFormats = self::DEFAULT_EXPORT_FORMATS;
  }

}
