<?php

namespace Drupal\eg_book\Controller;

use Drupal\Core\Controller\ControllerBase;

class BookController extends ControllerBase {

  public function getBook($book) {
    $publication_date = $book->field_publication_date->value;
    $body = $book->body->value;
    // Delays fully loading user. This field is multi-valued.
    $author_ids = [];
    $field_author_value = $book->field_author->getValue();
    foreach($field_author_value as $each) {
      $author_ids[] = $each['target_id'];
    }
    return [
      '#theme' => 'book',
      '#author' => $author_ids,
      '#publication_date' => $publication_date,
      '#body' => $body,
    ];
  }

  public function getTitle($book) {
    return [
      '#markup' => 'hainguyen',
    ];
  }

}
