<?php

declare(strict_types = 1);

namespace Drupal\sm_config\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DrupalKernelInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure SM Config routing for this site.
 */
final class SmRoutingConfigForm extends ConfigFormBase implements ContainerInjectionInterface {

  /**
   * @var array{'routing': array{'bus': string, 'message': string|class-string, 'receivers': string[]}}|null
   */
  private ?array $routingCache;

  /**
   * Constructs a new SymfonyMessengerConfigForm.
   */
  private function __construct(
    private array $messageBusMap,
    private array $receivers,
    private DrupalKernelInterface $kernel,
    ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct($configFactory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      messageBusMap: $container->getParameter('sm_config.message_bus_map'),
      // This will contain both alias and non-alias of receivers as defined by
      // \Symfony\Component\Messenger\DependencyInjection\MessengerPass::registerReceivers
      // 'messenger.receiver_locator')->replaceArgument(0).
      // I dont think it matters that we offer either.
      receivers: array_keys($container->get('messenger.receiver_locator')->getProvidedServices()),
      kernel: $container->get('kernel'),
      configFactory: $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sm_config_sm_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sm_config.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['help'] = [
      '#type' => 'inline_template',
      '#template' => <<<TEMPLATE
        <p>{{ 'Specify how messages are routed to transports.'|t }}</p>
        <p>{{ 'Transports may have duplicate service ID and transport aliases available for selection. Both are functionally equivalent, though choosing both from the same transport will predictably duplicate the message. It is unusual for more than one transport to be configured per message class; choose one if unsure.'|t }}</p>
        <p>{{ 'Some transports may not have an alias, so only the full service ID is available.'|t }}</p>
        <p>{{ 'When a message does not match a transport, it will execute immediately.'|t }}</p>
        TEMPLATE,
    ];

    $options = array_combine($this->receivers, $this->receivers);
    if (count($options) === 0) {
      $this->messenger()
        ->addWarning($this->t('There are no transports enabled. At least one transport must be enabled for this form to be usable.'));

      $existingConfig = $this->config('sm_config.settings')->get('routing');
      if (is_array($existingConfig) && count($existingConfig) > 0) {
        // Don't disable when there's existing config; allow user to resave new
        // new values which will likely unset the disabled transports.
        $this->messenger()
          ->addWarning($this->t('Existing configuration contains references to missing transports. Save this form again to resolve.'));
      }
      else {
        return $form;
      }
    }

    // @todo show original for merging strategy.
    $form['messages'] = [];
    $form['messages']['#tree'] = TRUE;

    foreach ($this->messageBusMap as $bus => $messages) {
      $map = array_combine($messages, $messages);
      $map += $this->commonPrefixes($messages) + [
        // The magic '*' type implemented by Symfony Messenger.
        // Messenger also supports partial match on class names.
        // See https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport
        '*' => $this->t('Fallback'),
      ];

      foreach ($map as $message => $messageLabel) {
        // Grouping.
        $form['messages'][$bus]['#type'] ??= 'details';
        $form['messages'][$bus]['#open'] ??= TRUE;
        $form['messages'][$bus]['#title'] ??= $bus;
        $isMessageClass = in_array($message, $messages, TRUE);
        [$group, $groupTitle] = match (TRUE) {
          $isMessageClass => ['message', $this->t('Message')],
          default => ('*' === $message
            ? ['fallback', $this->t('Fallback')]
            : ['prefix', $this->t('Class prefix')]),
        };
        $form['messages'][$bus][$group]['#type'] ??= 'fieldset';
        $form['messages'][$bus][$group]['#title'] ??= $groupTitle;
        $form['messages'][$bus][$group]['#description'] ??= match($group) {
          'message' => $this->t('Capture messages with exact message classes. Prefix and fallback options are ignored for messages with an exact class name match.'),
          'fallback' => $this->t('Acts as a default routing rule for any message not matched above. This is useful to ensure no messages are processed in the same request/thread as they are dispatched. See <a href=":link">documentation</a> for more information on wildcard routing.', [
            ':link' => 'https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport',
          ]),
          'prefix' => $this->t('Capture messages with these namespace prefixes. Exact or longer prefix-matches will have higher priority.'),
        };

        // Transport config.
        $form['messages'][$bus][$group][$message] = [
          '#title' => $isMessageClass
            ? $this->t('Transports for <code>@message</code>', [
              '@message' => $messageLabel,
            ])
            : $this->t('Transports for <em>@message</em>', [
              '@message' => $messageLabel,
            ]),
          '#type' => 'checkboxes',
          '#options' => $options,
          '#required' => FALSE,
          '#empty_option' => $this->t('- None -'),
          '#empty_value' => NULL,
          '#multiple' => TRUE,
          '#default_value' => $this->getDefaultValueForMessage($bus, $message),
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Get the default value for a message.
   */
  private function getDefaultValueForMessage(string $bus, string $message): array {
    $this->routingCache ??= $this->config('sm_config.settings')->get('routing');
    foreach (($this->routingCache ?? []) as ['bus' => $rBus, 'message' => $rMessage, 'receivers' => $rReceivers]) {
      if ($message === $rMessage && $bus === $rBus) {
        return $rReceivers;
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $routing = [];

    foreach ($form_state->getValue('messages') as $busName => $groups) {
      // Flatten map groupings and remove empty.
      $map = array_filter(array_merge(...array_values($groups)));
      /** @var array<class-string|string, string[]> $map */
      foreach ($map as $message => $receivers) {
        $receivers = array_filter($receivers);
        if (0 === count($receivers)) {
          // Don't add to configuration when there are no receivers.
          // This also prevents overriding container parameters with empty
          // receivers.
          continue;
        }

        $routing[] = [
          'bus' => $busName,
          'message' => $message,
          'receivers' => array_keys($receivers),
        ];
      }
    }

    $this->config('sm_config.settings')
      ->set('routing', $routing)
      ->save();

    $this->kernel->invalidateContainer();

    parent::submitForm($form, $form_state);
  }

  /**
   * Finds common namespace-piece prefixes.
   *
   * Only returns prefixes with at least two common prefix trails. For example
   * given:
   *  - Drupal\my_module\Foo\MyMessage
   *  - Drupal\my_module\MyMessage2
   *  - Drupal\my_module\MyMessage3
   * will return ['Drupal\my_module\*', 'Drupal\*'].
   *
   * @param class-string[] $messages
   *   An array of message class-strings.
   *
   * @return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup>
   *   An array of translation strings keyed by class prefixes.
   */
  private function commonPrefixes(mixed $messages): array {
    if (count($messages) === 0) {
      return [];
    }

    // Break message class FQN by slash.
    $splitMessageFqns = array_map(function (string $classString): array {
      return explode('\\', $classString);
    }, $messages);

    $commonPrefixes = [];
    // Determine the largest quantity of namespace pieces.
    $max = max(array_map('count', $splitMessageFqns));
    while ($max > 0) {
      // Slice all namespaces to the current level.
      $elmsAtLevel = array_map(static function ($item) use ($max): array {
        return array_slice($item, 0, $max);
      }, $splitMessageFqns);
      // And eliminate anything less than the current level.
      $elmsAtLevel = array_filter($elmsAtLevel, static function ($item) use ($max): bool {
        return count($item) === $max;
      });
      // Recombine elements to the sliced namespace at this level.
      $elmsAtLevel = array_map(static function ($item): string {
        return implode('\\', $item);
      }, $elmsAtLevel);

      // Determine which prefixes have at least one other duplicate.
      array_push($commonPrefixes, ...array_unique(array_diff_assoc($elmsAtLevel, array_unique($elmsAtLevel))));
      $max--;
    }

    $return = [];
    foreach ($commonPrefixes as $commonPrefix) {
      $prefix = $commonPrefix . '\\*';
      $return[$prefix] = $this->t('messages with class prefix: <code>@prefix</code>', [
        '@prefix' => $prefix,
      ]);
    }
    return $return;
  }

}
