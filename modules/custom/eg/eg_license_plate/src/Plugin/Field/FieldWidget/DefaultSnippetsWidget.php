<?php

namespace Drupal\eg_license_plate\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'default_snippets_widget' widget.
 *
 * @FieldWidget(
 *   id = "default_snippets_widget",
 *   label = @Translation("Default Snippets Widget"),
 *   field_types = {
 *     "snippets_code"
 *   }
 * )
 */
class DefaultSnippetsWidget extends WidgetBase {

  /**
   * {@inheritDoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['source_description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->source_description) ? $items[$delta]->source_description : NULL,
    ];
    $element['source_code'] = [
      '#title' => $this->t('Code'),
      '#type' => 'textarea',
      '#default_value' => isset($items[$delta]->source_code) ? $items[$delta]->source_code : NULL,
    ];
    $element['source_lang'] = [
      '#title' => $this->t('Source language'),
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->source_lang) ? $items[$delta]->source_lang : NULL,
    ];

    return $element;
  }

}
