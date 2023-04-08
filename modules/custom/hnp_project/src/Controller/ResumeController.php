<?php

namespace Drupal\hnp_project\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResumeController extends ControllerBase {

  /** 
   * Handles the `hnp_project.get_resume` route.
   */
  public function get() {
    $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties([
      'name' => 'Hai_Nguyen_resume.pdf',
    ]);

    if (!empty($media)) {
      $media = reset($media);
    }

    /** @var string $fid */
    $fid = $media->field_media_document->target_id;
    /** @var \Drupal\file\Entity\File */
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($fid);
    /** @var string $uri */
    $uri = $file->getFileUri();

    // This forces PDF download, which is not desired.
    // $headers = [
    //   'Content-Type'        => 'application/pdf',
    //   'Content-Disposition' => 'attachment;filename="download"'
    // ];

    $headers = [];

    return new BinaryFileResponse($uri, 200, $headers, true);
  }

}
