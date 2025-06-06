<?php

namespace Drupal\search_api_attachments\Plugin\search_api_attachments;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\search_api_attachments\TextExtractorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides tika server extractor.
 *
 * @SearchApiAttachmentsTextExtractor(
 *   id = "tika_server_extractor",
 *   label = @Translation("Tika JAX-RS Server Extractor"),
 *   description = @Translation("Adds Tika JAX-RS server extractor support."),
 * )
 */
class TikaServerExtractor extends TextExtractorPluginBase {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->httpClient = $container->get('http_client');
    return $instance;
  }

  /**
   * Extract file with a Tika JAX-RS Server.
   *
   * @param \Drupal\file\Entity\File $file
   *   A file object.
   *
   * @return string
   *   The text extracted from the file.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function extract(File $file) {
    $data = NULL;
    $options = [
      'timeout' => $this->configuration['timeout'],
      'body' => fopen($file->getFileUri(), 'r'),
      'headers'   => [
        'Accept' => 'text/plain',
      ],
    ];

    $response = $this->httpClient->request('PUT', $this->getServerUri() . '/tika', $options);
    if ($response->getStatusCode() === 200) {
      $data = (string) $response->getBody();
    }
    else {
      throw new \Exception('Tika JAX-RS Server is not available.');
    }

    return $data;
  }

  /**
   * Returns the Tika server URI from the current config.
   *
   * @return string
   *   The full Tika server URI.
   */
  protected function getServerUri() {
    return $this->configuration['scheme'] . '://' . $this->configuration['host'] . ':' . $this->configuration['port'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['scheme'] = [
      '#type' => 'select',
      '#title' => $this->t('HTTP protocol'),
      '#description' => $this->t('The HTTP protocol to use for sending queries.'),
      '#default_value' => $this->configuration['scheme'] ?? 'http',
      '#options' => [
        'http' => $this->t('http'),
        'https' => $this->t('https'),
      ],
    ];

    $form['host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tika server host'),
      '#description' => $this->t('The host name or IP of your Tika server, e.g. <code>localhost</code> or <code>www.example.com</code>.'),
      '#default_value' => $this->configuration['host'] ?? 'localhost',
      '#required' => TRUE,
    ];

    $form['port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tika server port'),
      '#description' => $this->t('The default port is 9998.'),
      '#default_value' => $this->configuration['port'] ?? '9998',
      '#required' => TRUE,
    ];

    $form['timeout'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 180,
      '#title' => $this->t('Query timeout'),
      '#description' => $this->t('The timeout in seconds for queries sent to the Tika server.'),
      '#default_value' => $this->configuration['timeout'] ?? 5,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (isset($values['text_extractor_config']['port'])) {
      $port = $values['text_extractor_config']['port'];
      if (!is_numeric($port) || $port < 0 || $port > 65535) {
        $form_state->setError($form['text_extractor_config']['port'], $this->t('The port has to be an integer between 0 and 65535.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['scheme'] = $form_state->getValue(['text_extractor_config', 'scheme']);
    $this->configuration['host'] = $form_state->getValue(['text_extractor_config', 'host']);
    $this->configuration['port'] = $form_state->getValue(['text_extractor_config', 'port']);
    $this->configuration['timeout'] = $form_state->getValue(['text_extractor_config', 'timeout']);
    parent::submitConfigurationForm($form, $form_state);
  }

}
