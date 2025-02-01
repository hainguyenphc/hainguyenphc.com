<?php

namespace Drupal\entity_reports\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\entity_reports\Event\EntityReportsExportFormats;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Route;

/**
 * Class EntityReportsRoutes.
 *
 * Defines routes.
 *
 * @package Drupal\entity_reports\Routing
 */
class EntityReportsRoutes implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The event dipatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * EntityReportsRoutes constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config, EventDispatcherInterface $event_dispatcher) {
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Returns available routes.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   Available routes.
   */
  public function getRoutes() {
    $routes = [];
    $configured_types = $this->config->get('entity_reports.settings')
      ->get('reported_entity_types');
    $event = new EntityReportsExportFormats();
    $this->eventDispatcher->dispatch($event, EntityReportsExportFormats::EVENT_NAME);
    $export_formats = array_keys($event->exportFormats);

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->entityClassImplements(FieldableEntityInterface::class)) {
        if (!empty($configured_types) && !in_array($entity_type_id, $configured_types, TRUE)) {
          continue;
        }
        // Add a route for the entity report page.
        $route = new Route("admin/reports/entity/$entity_type_id");
        $route
          ->setDefaults([
            '_title_callback' => '\Drupal\entity_reports\Controller\EntityReportsController::displayReportTitle',
            '_controller' => '\Drupal\entity_reports\Controller\EntityReportsController::displayReport',
            'entity_type' => $entity_type_id,
          ])
          ->setOption('_admin_route', TRUE)
          ->setRequirement('_permission', 'view entity reports');
        $routes["entity_reports.entity_structure.$entity_type_id"] = $route;

        // Add routes for the entity report file exports.
        foreach ($export_formats as $export_format) {
          $route = new Route("admin/reports/entity/$entity_type_id.$export_format");
          $route
            ->setDefaults([
              '_title_callback' => '\Drupal\entity_reports\Controller\EntityReportsController::displayReportTitle',
              '_controller' => '\Drupal\entity_reports\Controller\EntityReportsController::exportReport',
              'entity_type' => $entity_type_id,
              'export_format' => $export_format,
            ])
            ->setOption('_admin_route', TRUE)
            ->setRequirement('_permission', 'view entity reports');
          $routes["entity_reports.entity_structure.$entity_type_id.$export_format"] = $route;
        }

        // Add routes for the statistics report file exports.
        foreach ($export_formats as $export_format) {
          $route = new Route("admin/reports/entity_statistics.$export_format");
          $route
            ->setDefaults([
              '_title_callback' => '\Drupal\entity_reports\Controller\EntityReportsController::statisticsReportTitle',
              '_controller' => '\Drupal\entity_reports\Controller\EntityReportsController::exportStatisticsReport',
              'type' => $export_format,
            ])
            ->setOption('_admin_route', TRUE)
            ->setRequirement('_permission', 'view entity reports');
          $routes["entity_reports.statistics.$export_format"] = $route;
        }
      }
    }

    return $routes;
  }

}
