<?php

namespace Drupal\eg_salutation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\eg_salutation\HelloInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HelloWsController extends ControllerBase {

  /**
   * @var Drupal\eg_salutation\HelloInterface
   */
  protected $hello;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('eg_salutation.hello')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(HelloInterface $hello) {
    $this->hello = $hello;
  }

  public function hello(AccountInterface $user = NULL) {
    return [
      '#markup' => $this->hello->hello($user),
    ];
  }

  public function getTitle(AccountInterface $user) {
    $display_name = $user->getDisplayName();
    $content = "Greetings $display_name";
    return [
      '#markup' => $this->t($content),
    ];
  }

}
