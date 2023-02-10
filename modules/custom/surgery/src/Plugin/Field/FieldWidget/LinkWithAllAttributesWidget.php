<?php

namespace Drupal\surgery\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Drupal\link_attributes\LinkAttributesManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'link' widget.
 *
 * @FieldWidget(
 *   id = "link_all_attributes",
 *   label = @Translation("Link (with all attributes)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkWithAllAttributesWidget extends LinkWidget implements ContainerFactoryPluginInterface {

  /**
   * The link attributes manager.
   *
   * @var \Drupal\link_attributes\LinkAttributesManager
   */
  protected $linkAttributesManager;

  /**
   * Constructs a LinkWithAllAttributesWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\link_attributes\LinkAttributesManager $link_attributes_manager
   *   The link attributes manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, LinkAttributesManager $link_attributes_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->linkAttributesManager = $link_attributes_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.link_attributes')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'placeholder_url' => '',
      'placeholder_title' => '',
      'enabled_attributes' => [
        'device' => TRUE
      ],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $item = $items[$delta];
    $options = $item->get('options')->getValue();
    $attributes = isset($options['attributes']) ? $options['attributes'] : [];
    $element['options']['attributes'] = [
      '#type' => 'details',
      '#title' => $this->t('All Attributes for Link'),
      '#tree' => TRUE,
      '#open' => count($attributes),
    ];
    $plugin_definitions = $this->linkAttributesManager->getDefinitions();
    foreach (array_keys(array_filter($this->getSetting('enabled_attributes'))) as $attribute) {
      if (isset($plugin_definitions[$attribute])) {
        foreach ($plugin_definitions[$attribute] as $property => $value) {
          if ($property === 'id') {
            // Don't set ID.
            continue;
          }
          $element['options']['attributes'][$attribute]['#' . $property] = $value;
        }
        // Set the default value, in case of a class that is stored as array,
        // convert it back to a string.
        $default_value = isset($attributes[$attribute]) ? $attributes[$attribute] : NULL;
        if ($attribute === 'class' && is_array($default_value)) {
          $default_value = implode(' ', $default_value);
        }
        if (isset($default_value)) {
          $element['options']['attributes'][$attribute]['#default_value'] = $default_value;
        }
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $options = array_map(function ($plugin_definition) {
      return $plugin_definition['title'];
    }, $this->linkAttributesManager->getDefinitions());
    $selected = array_keys(array_filter($this->getSetting('enabled_attributes')));
    $element['enabled_attributes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Attributes to enable'),
      '#options' => $options,
      '#default_value' => array_combine($selected, $selected),
      '#description' => $this->t('Select the attributes to allow the user to edit.'),
    ];
    return $element;
  }

}