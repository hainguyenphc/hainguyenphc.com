<?php

namespace Drupal\eg_logger\Logger;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Psr\Log\LoggerInterface;

/**
 * @see web/core/modules/dblog/src/Logger/DbLog.php
 * @see web/core/modules/syslog/src/Logger/SysLog.php
 */
class MailLogger implements LoggerInterface {

  // This does all heavy lifting for us with impl for `alert`, etc.
  use RfcLoggerTrait;

  /** @var Drupal\Core\Logger\LogMessageParserInterface */
  protected $parser;

  /** @var Drupal\Core\Config\ConfigFactoryInterface */
  protected $config_factory;

  public function __construct(
    LogMessageParserInterface $parser,
    ConfigFactoryInterface $config_factory
  ) {
    $this->parser = $parser;
    $this->config_factory = $config_factory;
  }

  public function log($level, $message, array $context = array()) {
    // If there is no error, then we do not send out email.
    if ($level !== RfcLogLevel::ERROR) {
      return;
    }

    $to = $this->config_factory->get('system.site')->get('mail');
    $langcode = $this->config_factory->get('system.site')->get('langcode');
    $variables = $this->parser->parseMessagePlaceholders($message, $context);
    $markup = new FormattableMarkup($message, $variables);
    /**
     * - 1st param is name of the module implementing hook_mail().
     * - 2nd param is the key ID or template ID to use.
     * - 5th param is $params array passed to hook_mail().
     *
     * @see eg_email_mail().
     */
    \Drupal::service('plugin.manager.mail')
      ->mail('eg_email', 'create_article', $to, $langcode, ['message' => $markup]);
  }

}
