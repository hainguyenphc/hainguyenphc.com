<?php

declare(strict_types = 1);

namespace Drupal\sm;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Compiler pass for #[AsMessageHandler] autoconfiguration.
 */
final class SmMessageHandlerCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    // Creates services for message handlers.
    foreach ($this->getMessengerHandlerClasses($container->getParameter('container.namespaces')) as $className) {
      // Don't create a service definition if this class is already a service.
      if ($container->hasDefinition($className)) {
        continue;
      }

      // registerAttributeForAutoconfiguration requires services to be created
      // beforehand. Symfony normally creates services for everything in src/.
      $definition = new Definition($className);
      $definition
        // Note: Autoconfigure does not work in services.yml file in < Drupal 11
        // See https://www.drupal.org/project/drupal/issues/3221128
        ->setAutoconfigured(TRUE)
        ->setAutowired(TRUE)
        ->setPublic(FALSE);

      $anonymousHash = ContainerBuilder::hash($className . mt_rand());
      $container->setDefinition('.' . $anonymousHash, $definition);
    }

    // Registers classes/methods with AsMessageHandler as a message handler.
    // Pulled from Symfony' FrameworkExtension.
    $container->registerAttributeForAutoconfiguration(AsMessageHandler::class, static function (ChildDefinition $definition, AsMessageHandler $attribute, \Reflector $reflector): void {
      $tagAttributes = get_object_vars($attribute);
      $tagAttributes['from_transport'] = $tagAttributes['fromTransport'];
      unset($tagAttributes['fromTransport']);
      if ($reflector instanceof \ReflectionMethod) {
        if (isset($tagAttributes['method'])) {
          throw new LogicException(sprintf('AsMessageHandler attribute cannot declare a method on "%s::%s()".', $reflector->class, $reflector->name));
        }
        $tagAttributes['method'] = $reflector->getName();
      }
      $definition->addTag('messenger.message_handler', $tagAttributes);
    });
  }

  /**
   * Get MessengerHandler classes for the provided namespaces.
   *
   * @param array<class-string, string> $namespaces
   *   An array of namespaces. Where keys are class strings and values are
   *   paths.
   *
   * @return \Generator<class-string>
   *   Generates class strings.
   *
   * @throws \ReflectionException
   */
  private function getMessengerHandlerClasses(array $namespaces): \Generator {
    foreach ($namespaces as $namespace => $dirs) {
      $dirs = (array) $dirs;
      foreach ($dirs as $dir) {
        $dir .= '/Messenger';
        if (!file_exists($dir)) {
          continue;
        }
        $namespace .= '\\Messenger';

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $fileinfo) {
          if ($fileinfo->getExtension() !== 'php') {
            continue;
          }

          /** @var \RecursiveDirectoryIterator|null $subDir */
          $subDir = $iterator->getSubIterator();
          if (NULL === $subDir) {
            continue;
          }

          $subDir = $subDir->getSubPath();
          $subDir = $subDir !== '' ? str_replace(DIRECTORY_SEPARATOR, '\\', $subDir) . '\\' : '';
          $class = $namespace . '\\' . $subDir . $fileinfo->getBasename('.php');

          $reflectionClass = new \ReflectionClass($class);

          if (count($reflectionClass->getAttributes(AsMessageHandler::class)) > 0) {
            yield $class;
            continue;
          }

          $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
          foreach ($methods as $reflectionMethod) {
            if (count($reflectionMethod->getAttributes(AsMessageHandler::class)) > 0) {
              yield $class;
              continue 2;
            }
          }
        }
      }
    }
  }

}
