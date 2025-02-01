<?php

namespace Drupal\entity_reports\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is fired when Entity Reports is processing an export.
 */
class EntityReportsExportProcessors extends Event {

  const EVENT_NAME = 'entity_reports_export_processors';

  /**
   * Report content to be exported.
   *
   * @var array
   */
  public $content;

  /**
   * Type of entity described by report.
   *
   * @var string
   */
  public $entityType;

  /**
   * List of report fields.
   *
   * @var array
   */
  public $reportFields;

  /**
   * The format of the report to be exported.
   *
   * @var string
   */
  public $type;

  /**
   * Response headers.
   *
   * @var array
   */
  public $responseHeaders;

  /**
   * Constructs the object.
   *
   * @param array $content
   *   Actual content to render.
   * @param string $entity_type
   *   Type of entity to export.
   * @param array $report_fields
   *   Name of the fields to export.
   * @param string $type
   *   Output format (XML, JSON etc.)
   */
  public function __construct(array $content, $entity_type, array $report_fields, $type) {
    $this->content = $content;
    $this->entityType = $entity_type;
    $this->reportFields = $report_fields;
    $this->type = $type;
    $this->responseHeaders = [
      'Content-Type' => 'application/' . $type,
    ];
  }

}
