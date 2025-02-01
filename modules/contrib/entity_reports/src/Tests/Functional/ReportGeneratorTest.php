<?php

namespace Drupal\entity_reports\Tests\Functional;

/**
 * Class ReportGeneratorTest.
 *
 * Helper class for testing report generation.
 *
 * @package Drupal\entity_reports\Tests\Functional
 * @group entity_reports
 */
class ReportGeneratorTest extends EntityReportsTestBase {

  /**
   * Modules.
   *
   * @var array
   */
  protected static $modules = ['entity_reports'];

  /**
   * Setup.
   *
   * Test setup.
   *
   * @throws \Exception
   */
  public function setUp(): void {
    parent::setUp();
    $fields = [
      'body' => [
        'label' => 'Body',
        'description' => 'Body description',
        'type' => 'text_textarea_with_summary',
        'required' => TRUE,
        'translatable' => TRUE,
        'settings' => [
          'required' => TRUE,
          'display_summary' => TRUE,
          'translatable' => TRUE,
        ],
      ],
    ];

    $this->createSampleContentType([
      'name' => 'Basic page',
      'description' => 'The Basic page',
      'type' => 'page',
      'display_submitted' => FALSE,
    ], $fields);
  }

  /**
   * Test export.
   */
  public function testExport() {
    /** @var \Drupal\entity_reports\ReportGenerator $service */
    $service = \Drupal::service('entity_reports.generator');
    $report = $service->generateEntityFieldsReport('node', 'page');
    $report_without_subfields = array_filter($report, function ($key) {
      return strpos($key, '.') === FALSE;
    }, ARRAY_FILTER_USE_KEY);
    $this->assertEquals(1, count($report_without_subfields));
    $this->assertEquals(4, count($report));

    $body = $report['body'];
    $this->assertEquals('Body', $body['label']);
    $this->assertEquals('body', $body['machine_name']);
    $this->assertEquals('Body description', $body['description']);
    $this->assertEquals('text_with_summary', $body['type']);
    $this->assertTrue($body['required']);
    $this->assertEquals('True', $body['required_human']);
    $this->assertTrue($body['translatable']);
    $this->assertEquals('True', $body['translatable_human']);
    $this->assertEquals('', $body['target']);
    $this->assertEquals(1, $body['cardinality']);
    $this->assertEquals('One value', (string) $body['cardinality_human']);
  }

}
