<?php

namespace Drupal\eg_salutation\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\eg_salutation\Hello;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *  id = "eg_salutation_hello_config_form_block",
 *  admin_label = @Translation("Hello Config Form Block"),
 *  category = @Translation("Example")
 * )
 */
class HelloBlock extends BlockBase implements ContainerFactoryPluginInterface {
  // BlockBase::build() && ContainerFactoryPluginInterface::create()

  /**
   * @var Drupal\eg_salutation\HelloInterface $hello.
   */
  protected $hello;

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Feel free to use the service 'eg_salutation.hello'
    // e.g. $this->hello->hetllo($user);

    // Injects the whole form into the block.
    $form_builder = \Drupal::formBuilder();
    $form = $form_builder->getForm('Drupal\eg_salutation\Form\HelloConfigForm');

    // Alters the form block based on configuration.
    // Writes CSS to visually decorate the block.
    // @see defaultConfiguration()
    $config = $this->getConfiguration();
    if ($config['decorated'] && $config['decorated'] === 1) {
      $form['#attributes']['class'][] = 'bordered';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration, $plugin_id, $plugin_definition, Hello $hello
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->hello = $hello;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('eg_salutation.hello')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'decorated' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form['decorated'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Decorate this form'),
      '#description' => $this->t('Check this box to decorate the form.'),
      '#default_value' => $config['decorated'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // First approach
    // $this->configuration['decorated'] = $form_state->getValue('decorated');

    // Second approach
    $this->setConfiguration([
      'decorated' => $form_state->getValue('decorated')
    ]);
  }

}
