<?php

namespace Drupal\eg_license_plate\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'default_snippets_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "default_snippets_formatter",
 *   label = @Translation("Default Snippets Formatter"),
 *   field_types = {
 *     "snippets_code"
 *   }
 * )
 */
class DefaultSnippetsFormatter extends FormatterBase {

  /**
   * {@inheritDoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // Render output using snippets_default theme.
      $source = [
        '#theme' => 'snippets_code',
        '#source_code' => $item->source_code,
        '#source_lang' => $item->source_lang,
        '#source_description' => $item->source_description,
      ];
      
      $elements[$delta] = [
        '#markup' => \Drupal::service('renderer')->render($source)
      ];
    }

    return $elements;
  }

}
