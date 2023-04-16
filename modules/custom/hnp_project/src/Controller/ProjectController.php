<?php

namespace Drupal\hnp_project\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProjectController extends ControllerBase {

  /** 
   * Handles `hnp_project.get_projects_by_category` route.
   */
  public function getProjectsByCategory(string $category) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'project')
      ->condition('field_project_categories.entity:taxonomy_term.field_machine_name', $category)
      ->accessCheck(FALSE);
    $nodes = $query->execute();

    $data = [];

    foreach ($nodes as $nid) {
      $node = \Drupal\node\Entity\Node::load($nid);
      $datum = [];
      $datum['nid'] = $node->id();
      $datum['machine_name'] = $node->field_machine_n->value;
      $datum['title'] = $node->getTitle();
      $datum['body'] = $node->body->value;
      $datum['url_alias'] = $node->toUrl()->toString();
      $datum['build_steps'] = $this->_getBuildStepsByProject($node->field_machine_n->value);
      $data[] = $datum;
    }

    return new JsonResponse(['data' => $data]);
  }

  /** 
   * Handles `hnp_project.get_project` route.
   */
  public function getProject($project) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'project')
      ->condition('field_machine_n', $project)
      ->accessCheck(FALSE);
    $nodes = $query->execute();

    if (is_array($nodes)) {
      $nodes = reset($nodes);
    }

    $node = \Drupal\node\Entity\Node::load($nodes);
    
    $datum = [];
    $datum['nid'] = $node->id();
    $datum['machine_name'] = $node->field_machine_n->value;
    $datum['title'] = $node->getTitle();
    $datum['body'] = $node->body->value;

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'project_build_step')
      ->condition('field_project.entity:node.nid', $node->id())
      ->range(0, 3)
      ->accessCheck(FALSE);
    $results = $query->execute();

    $related = \Drupal\node\Entity\Node::loadMultiple($results);
    if (!empty($related)) {
      /** @var \Drupal\image\Entity\ImageStyle $image_style */
      $image_style = \Drupal::entityTypeManager()->getStorage('image_style')->load('medium');
      foreach ($related as $each) {
        $teaser_image_uri = \Drupal\file\Entity\File::load($each->field_teaser_image->target_id)->getFileUri();
        $teaser_image_url = $image_style->buildUrl($teaser_image_uri);
        $datum['related'][$each->id()] = [
          'id' => $each->id(),
          'url' => $each->toUrl()->toString(),
          'title' => $each->getTitle(),
          'teaser_image_url' => $teaser_image_url,
        ];
      }
    }

    return new JsonResponse(['data' => $datum]);
  }

  /** 
   * Handles `hnp_project.get_build_steps_by_project` route.
   */
  public function getBuildStepsByProject($project) {
    $data = $this->_getBuildStepsByProject($project);
    return new JsonResponse(['data' => $data]);
  }

  /** 
   * Handles `hnp_project.get_build_step` route.
   */
  public function getBuildStep($build_step) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'project_build_step')
      ->condition('field_machine_n', $build_step)
      ->accessCheck(FALSE);
    $nodes = $query->execute();

    if (is_array($nodes)) {
      $nodes = reset($nodes);
    }
    /** @var \Drupal\node\Entity\Node $node */
    $node = \Drupal\node\Entity\Node::load($nodes);

    $datum = [];
    $datum['nid'] = $node->id();
    $datum['machine_name'] = $node->field_machine_n->value;
    $datum['title'] = $node->getTitle();
    $datum['body'] = $node->body->value;

    $related = $node->field_related_build_steps->referencedEntities();
    if (!empty($related)) {
      /** @var \Drupal\image\Entity\ImageStyle $image_style */
      $image_style = \Drupal::entityTypeManager()->getStorage('image_style')->load('medium');
      foreach ($related as $each) {
        $id = $each->id();
        if ($id == $node->id()) { continue; }
        $teaser_image_uri = \Drupal\file\Entity\File::load($each->field_teaser_image->target_id)->getFileUri();
        $teaser_image_url = $image_style->buildUrl($teaser_image_uri);
        $datum['related'][$id] = [
          'id' => $id,
          'url' => $each->toUrl()->toString(),
          'title' => $each->getTitle(),
          'teaser_image_url' => $teaser_image_url,
        ];
      }
    }

    return new JsonResponse(['data' => $datum]);
  }

  /*********************************************
   * Helper functions
   *********************************************/

  public function _getBuildStepsByProject($project) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'project_build_step')
      ->condition('field_project.entity:node.field_machine_n', $project)
      ->accessCheck(FALSE);
    $nodes = $query->execute();

    $data = [];

    foreach ($nodes as $nid) {
      $node = \Drupal\node\Entity\Node::load($nid);
      $datum = [];
      $datum['nid'] = $node->id();
      $datum['title'] = $node->getTitle();
      $datum['url_alias'] = $node->toUrl()->toString();
      $data[] = $datum;
    }

    return $data;
  }

}
