<?php

namespace Drupal\eg_form_apis\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\DatabaseConnectionRefusedException;
use Drupal\Core\Database\IntegrityConstraintViolationException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

class DownloadRemoteManagedFile extends FormBase {

  const SITE_MAPPING = [
    'kaufman' => 'kaufman-hall',
  ];

  /**
   * The Guzzle HTTP Client service.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  protected $drupalSites;

  /**
   * Constructor
   * 
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(Client $httpClient, $fileSystem) {
    $this->httpClient = $httpClient;
    $this->fileSystem = $fileSystem;
    $this->drupalSites = [
      'kaufman' => $this->t('Kaufman Hall'),
    ];
  }

  /** 
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('file_system'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'eg_form_apis.download_remote_managed_file';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['drupal_site'] = [
      '#type' => 'select',
      '#title' => $this->t('Drupal site'),
      '#options' => $this->drupalSites,
      '#required' => TRUE,
      '#after_build' => [
        [$this, 'startSitesLocally'],
      ],
    ];

    $form['filename'] = [
      '#type' => 'textfield',
      '#title' => $this->t('File name'),
      '#tile_display' => 'before',
      '#default_value' => 'site-neutral-payments-thumb',
      '#required' => TRUE,
      '#required_error' => $this->t('We need to know the file name to download'),
      '#states' => [
        'visible' => [
          ':input[name="drupal_site"]' => [
            '!value' => ''
          ],
        ],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  function submitForm(array &$form, FormStateInterface $form_state) {
    $site = $form_state->getValue('drupal_site');
    $site = $this::SITE_MAPPING[$site];
    $filename = $form_state->getValue('filename');
    $connection_info = [
      'database' => 'db',
      'username' => 'db',
      'password' => 'db',
      'host' => "ddev-{$site}-db",
      'port' => 3306,
      'driver' => 'mysql',
    ];
    Database::addConnectionInfo('kaufman', 'default', $connection_info);
    $external_connection = Database::getConnection('default', 'kaufman');
    try {
      $results = $external_connection
        ->query("SELECT fid, uri 
          FROM file_managed 
          WHERE filename LIKE '%{$filename}%'")
        ->fetchAll();
      if (is_array($results) && !empty($results)) {
        foreach ($results as $result) {
          // I.e "public://images/2021-09/site-neutral-payments-thumb.jpg"
          $uri = $result->uri;
          // I.e "https://www.kaufmanhall.com/sites/default/files/images/2021-09/site-neutral-payments-thumb.jpg"
          $source = str_replace('public://', 'https://www.kaufmanhall.com/sites/default/files/', $uri);
          // I.e "sites/default/files/images/2021-09/site-neutral-payments-thumb.jpg"
          $destination = str_replace('public://', 'sites/default/files/', $uri);
          // I.e "sites/default/files/images/2021-09/site-neutral-payments-thumb.jpg"
          $final_destination = $this->fileSystem->getDestinationFilename($destination, FileSystemInterface::EXISTS_REPLACE);

          $destination_stream = @fopen($final_destination, 'w');
          if (!$destination_stream) {
            $directory = $this->fileSystem->dirname($final_destination);
            if (!$this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
              throw new \Exception("Cannot prepare directory {$directory}.");
            }
            $destination_stream = @fopen($final_destination, 'w');
            if (!$destination_stream) {
              throw new \Exception("Cannot write to destination file {$final_destination}.");
            }
          }

          try {
            $response = $this->httpClient->get($source, ['sink' => $final_destination]);
            if ($response->getStatusCode() == 200) {
              $message = $this->t("The file {$filename} was successfully downloaded.");
              $this->messenger()->addMessage($message);
              $this->logger('eg_form_apis')->log(RfcLogLevel::INFO, $message);
            }
          }
          catch (GuzzleException $exception) {
            throw $exception;
          }
          catch (\Exception $exception) {
            throw $exception;
          }
        }
      }
    }
    catch (DatabaseConnectionRefusedException $exception) {
      throw $exception;
    }
    catch (IntegrityConstraintViolationException $exception) {
      throw $exception;
    }
    catch (InvalidArgumentException $exception) {
      throw $exception;
    }
    catch (\Exception $exception) {
      throw $exception;
    }
  }

}
