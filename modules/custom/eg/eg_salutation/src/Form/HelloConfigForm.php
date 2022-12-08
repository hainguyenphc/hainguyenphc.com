<?php

namespace Drupal\eg_salutation\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HelloConfigForm extends ConfigFormBase {
  //'getEditableConfigNames', 'getFormId' are required.

  /** @var Drupal\Core\Logger\LoggerChannelInterface */
  protected $logger_channel;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LoggerChannelInterface $logger_channel
  ) {
    parent::__construct($config_factory);
    $this->logger_channel = $logger_channel;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('config.factory'),
      $container->get('eg_logger.logger.channel.eg_logger'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'eg_salutation.show_emoji_face',
      'eg_salutation.supplement'
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Note: the string should not include a dot (.) since the form Id is used in
   * hook_form_FORM_ID_alter().
   */
  public function getFormId() {
    return 'eg_salutation_hello_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $show_emoji_config = $this->config('eg_salutation.show_emoji_face');
    $show_emoji = $show_emoji_config->get('show_emoji') ? TRUE : FALSE;
    $supplement_config = $this->config('eg_salutation.supplement');
    $supplement = $supplement_config->get('supplement')? : '';
    $form = [
      'show_emoji' => [
        '#type' => 'checkbox',
        '#title' => 'Show Emoji face',
        '#description' => 'If checked, greeting message has emoji next to it.',
        '#default_value' => $show_emoji,
      ],
      'supplement' => [
        '#type' => 'textfield',
        '#title' => 'Supplement to greeting message',
        '#description' => 'This text will show up after the greeting message',
        '#default_value' => $supplement,
      ],
    ];
    // The parent adds submit button element for us.
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('eg_salutation.show_emoji_face')
      ->set('show_emoji', $form_state->getValue('show_emoji'))
      ->save();

    $this->config('eg_salutation.supplement')
      ->set('supplement', $form_state->getValue('supplement'))
      ->save();

    // Feel free to trigger anything here.
    $this->logger_channel
      ->info("The supplement of salutation has been changed to @message", [
        '@message' => $form_state->getValue('supplement'),
      ]);

    // The parent shows success message, etc.
    parent::submitForm($form, $form_state);
  }

}
