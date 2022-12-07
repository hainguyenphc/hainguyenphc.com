<?php

namespace Drupal\eg_mail_plugin\Plugin\Mail;

use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Mail(
 *  id = 'hello_world_mail',
 *  label = @Translation('Hello World Mailer'),
 *  description = @Translation('Sends emails with external API.')
 * )
 */
class HelloWorldMail implements MailInterface, ContainerFactoryPluginInterface {

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    // Just a demo, there is no dependency to inject.
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    $message['body'] = implode("\n\n", $message['body']);
    $message['body'] = MailFormatHelper::htmlToText($message['body']);
    $message['body'] = MailFormatHelper::wrapMail($message['body']);
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    // Use external API to send emails here.
    // $message is constructed in hook_mail impl.
  }

}
