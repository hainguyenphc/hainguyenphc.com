<?php

namespace Drupal\search_api_attachments\Plugin\search_api_attachments;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\search_api_attachments\TextExtractorPluginBase;

/**
 * Provides tika extractor.
 *
 * @SearchApiAttachmentsTextExtractor(
 *   id = "tika_extractor",
 *   label = @Translation("Tika Extractor"),
 *   description = @Translation("Adds Tika extractor support."),
 * )
 */
class TikaExtractor extends TextExtractorPluginBase {

  /**
   * Extract file with Tika library.
   *
   * @param \Drupal\file\Entity\File $file
   *   A file object.
   *
   * @return string
   *   The text extracted from the file.
   */
  public function extract(File $file) {
    $output = '';
    $filepath = $this->getRealpath($file->getFileUri());
    $tika = realpath($this->configuration['tika_path']);
    $java = $this->configuration['java_path'];
    // UTF-8 multibyte characters will be stripped by escapeshellargs() for the
    // default C-locale.
    // So temporarily set the locale to UTF-8 if available so that the filepath
    // remains valid.
    // Attempt to use en_US.UTF-8 first, and fallback to C.UTF-8, since
    // en_US.UTF-8 is more widespread, while C.UTF-8 may be present otherwise.
    $backup_locale = setlocale(LC_CTYPE, '0');
    $preferred_locales = ['en_US.UTF-8', 'C.UTF-8'];
    $new_locale = setlocale(LC_CTYPE, $preferred_locales) ?: $backup_locale;

    $param = '';
    if ($file->getMimeType() != 'audio/mpeg') {
      $param = ' -Dfile.encoding=UTF8 -cp ' . escapeshellarg($tika);
    }

    // Force running the Tika jar headless.
    $param = ' -Djava.awt.headless=true ' . $param;

    $cmd = $java . $param . ' -jar ' . escapeshellarg($tika) . ' -t ' . escapeshellarg($filepath);
    if (strpos(ini_get('extension_dir'), 'MAMP/')) {
      $cmd = 'export DYLD_LIBRARY_PATH=""; ' . $cmd;
    }
    // Restore the locale.
    setlocale(LC_CTYPE, $backup_locale);
    // Support UTF-8 encoded filenames.
    $cmd = 'export LANG=' . $new_locale . '; ' . $cmd;
    if ($this->configuration['debug_mode']) {
      $cmd .= ' 2>/dev/null';
    }
    $output = shell_exec($cmd);
    if (is_null($output)) {
      throw new \Exception('Tika Extractor is not available.');
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['java_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to java executable'),
      '#description' => $this->t('Enter the path to java executable. Example: "java".'),
      '#default_value' => $this->configuration['java_path'],
      '#required' => TRUE,
    ];
    $form['tika_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to Tika .jar file'),
      '#description' => $this->t('Enter the full path to tika executable jar file. Example: "/var/apache-tika/tika-app-1.8.jar".'),
      '#default_value' => $this->configuration['tika_path'],
      '#required' => TRUE,
    ];
    $form['debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug mode'),
      '#description' => $this->t('Enable debug mode to display Tika warnings.'),
      '#default_value' => $this->configuration['debug_mode'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue(['text_extractor_config']);
    $java_path = $values['java_path'];
    $tika_path = $values['tika_path'];

    // Check java path.
    exec($java_path, $output, $return_code);
    // $return_code = 127 if it fails. 1 instead.
    if ($return_code != 1) {
      $form_state->setError($form['text_extractor_config']['java_path'], $this->t('Invalid path or filename %path for java executable.', ['%path' => $java_path]));
      return;
    }

    // Check tika path.
    if (!file_exists($tika_path)) {
      $form_state->setError($form['text_extractor_config']['tika_path'], $this->t('Invalid path or filename %path for tika application jar.', ['%path' => $tika_path]));
    }
    // Check return code.
    else {
      $cmd = $java_path . ' -jar ' . escapeshellarg($tika_path) . ' -V';
      exec($cmd, $output, $return_code);
      // $return_code = 1 if it fails. 0 instead.
      if ($return_code) {
        $form_state->setError($form['text_extractor_config']['tika_path'], $this->t('Tika could not be reached and executed.'));
      }
      else {
        $this->getMessenger()
          ->addStatus($this->t('Tika can be reached and be executed'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['java_path'] = $form_state->getValue(['text_extractor_config', 'java_path']);
    $this->configuration['tika_path'] = $form_state->getValue(['text_extractor_config', 'tika_path']);
    $this->configuration['debug_mode'] = $form_state->getValue(['text_extractor_config', 'debug_mode']);
    parent::submitConfigurationForm($form, $form_state);
  }

}
