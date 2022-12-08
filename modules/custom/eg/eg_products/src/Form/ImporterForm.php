<?php

namespace Drupal\eg_products\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\eg_products\Plugin\ImporterPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImporterForm extends EntityForm {

  /**
   * @var Drupal\eg_products\Plugin\ImporterPluginManager
   */
  protected $importer_plugin_manager;

  /**
   * @var Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entity_type_manager;

  public function __construct(
    ImporterPluginManager $importer_plugin_manager,
    MessengerInterface $messenger,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->importer_plugin_manager = $importer_plugin_manager;
    $this->messenger = $messenger;
    $this->entity_type_manager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('products.importer_manager'),
      $container->get('messenger'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\eg_products\Entity\Importer */
    $importer = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $importer->label(),
      '#description' => $this->t('Name of importer'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#disabled' => !($importer->isNew()),
      '#default_value' => $importer->id(),
      '#machine_name' => [
        'exists' => '\Drupal\eg_products\Entity\Importer::load',
      ],
    ];

    $source_url = $importer->getSourceUrl();
    $form['source_url'] = [
      '#type' => 'url',
      '#default_value' =>
        ($source_url instanceof Url) ? ($source_url ->toString()) : '',
      '#title' => $this->t('Source Url'),
      '#description' => $this->t('The source URL to the import resource'),
      '#required' => TRUE,
    ];

    $definitions = $this->importer_plugin_manager->getDefinitions();
    $options = [];
    foreach ($definitions as $id => $definition) {
      $options[$id] = $definition['label'];
    }

    $form['plugin_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Plugin'),
      '#default_value' => $importer->getPluginId(),
      '#options' => $options,
      '#description' => $this->t('The plugin to be used with this importer.'),
      '#required' => TRUE,
    ];

    $form['update_existing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Update existing?'),
      '#description' => $this->t('Whether to update existing products if already imported.'),
      '#default_value' => $importer->updateExistingProducts(),
    ];

    $form['source'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source'),
      '#description' => $this->t('The source of the products.'),
      '#default_value' => $importer->getSource(),
    ];

    $bundle_ = NULL;
    $bundle_ = $importer->getBundle();
    if ($bundle_) {
      $bundle_ = $this->entity_type_manager
        ->getStorage('product_type')
        ->load($bundle_);
    }
    $form['bundle'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Product Type (bundle)'),
      '#target_type' => 'product_type',
      '#default_value' => $bundle_,
      '#description' => $this->t('The type of product (goods or services) to be created.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\eg_products\Entity\Importer $importer */
    $importer = $this->entity;
    $status = $importer->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger->addMessage($this->t('Created the %label Importer.', [
          '%label' => $importer->label(),
        ]));
        break;

      default:
        $this->messenger->addMessage($this->t('Saved the %label Importer.', [
          '%label' => $importer->label(),
        ]));
    }

    $form_state->setRedirectUrl($importer->toUrl('collection'));
  }

}
