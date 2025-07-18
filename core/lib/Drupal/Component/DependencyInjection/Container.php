<?php

namespace Drupal\Component\DependencyInjection;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Provides a container optimized for Drupal's needs.
 *
 * This container implementation is compatible with the default Symfony
 * dependency injection container and similar to the Symfony ContainerBuilder
 * class, but optimized for speed.
 *
 * It is based on a PHP array container definition dumped as a
 * performance-optimized machine-readable format.
 *
 * The best way to initialize this container is to use a Container Builder,
 * compile it and then retrieve the definition via
 * \Drupal\Component\DependencyInjection\Dumper\OptimizedPhpArrayDumper::getArray().
 *
 * The retrieved array can be cached safely and then passed to this container
 * via the constructor.
 *
 * As the container is unfrozen by default, a second parameter can be passed to
 * the container to "freeze" the parameter bag.
 *
 * This container is different in behavior from the default Symfony container in
 * the following ways:
 *
 * - It only allows lowercase service and parameter names, though it does only
 *   enforce it via assertions for performance reasons.
 * - The following functions, that are not part of the interface, are explicitly
 *   not supported: getParameterBag(), isFrozen(), compile(),
 *   getAServiceWithAnIdByCamelCase().
 * - The function getServiceIds() was added as it has a use-case in core and
 *   contrib.
 *
 * @ingroup container
 */
class Container implements ContainerInterface, ResetInterface {

  use ServiceIdHashTrait;

  /**
   * The parameters of the container.
   *
   * @var array
   */
  protected $parameters = [];

  /**
   * The aliases of the container.
   *
   * @var array
   */
  protected $aliases = [];

  /**
   * The service definitions of the container.
   *
   * @var array
   */
  protected $serviceDefinitions = [];

  /**
   * The instantiated services.
   *
   * @var array
   */
  protected $services = [];

  /**
   * The instantiated private services.
   *
   * @var array
   */
  protected $privateServices = [];

  /**
   * The currently loading services.
   *
   * @var array
   */
  protected $loading = [];

  /**
   * Whether the container parameters can still be changed.
   *
   * For testing purposes the container needs to be changed.
   *
   * @var bool
   */
  protected $frozen = TRUE;

  /**
   * Constructs a new Container instance.
   *
   * @param array $container_definition
   *   An array containing the following keys:
   *   - aliases: The aliases of the container.
   *   - parameters: The parameters of the container.
   *   - services: The service definitions of the container.
   *   - frozen: Whether the container definition came from a frozen
   *     container builder or not.
   *   - machine_format: Whether this container definition uses the optimized
   *     machine-readable container format.
   */
  public function __construct(array $container_definition = []) {
    if (!empty($container_definition) && (!isset($container_definition['machine_format']) || $container_definition['machine_format'] !== TRUE)) {
      throw new InvalidArgumentException('The non-optimized format is not supported by this class. Use an optimized machine-readable format instead, e.g. as produced by \Drupal\Component\DependencyInjection\Dumper\OptimizedPhpArrayDumper.');
    }

    $this->aliases = $container_definition['aliases'] ?? [];
    $this->parameters = $container_definition['parameters'] ?? [];
    $this->serviceDefinitions = $container_definition['services'] ?? [];
    $this->frozen = $container_definition['frozen'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function get($id, $invalid_behavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): ?object {
    if ($this->hasParameter('_deprecated_service_list')) {
      if ($deprecation = $this->getParameter('_deprecated_service_list')[$id] ?? '') {
        @trigger_error($deprecation, E_USER_DEPRECATED);
      }
    }
    if (isset($this->aliases[$id])) {
      $id = $this->aliases[$id];
    }

    // Re-use shared service instance if it exists.
    if (isset($this->services[$id]) || ($invalid_behavior === ContainerInterface::NULL_ON_INVALID_REFERENCE && array_key_exists($id, $this->services))) {
      return $this->services[$id];
    }

    if ($id === 'service_container') {
      return $this;
    }

    if (isset($this->loading[$id])) {
      throw new ServiceCircularReferenceException($id, array_keys($this->loading));
    }

    $definition = $this->serviceDefinitions[$id] ?? NULL;

    if (!$definition && $invalid_behavior === ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE) {
      if (!$id) {
        throw new ServiceNotFoundException('');
      }

      throw new ServiceNotFoundException($id, NULL, NULL, $this->getServiceAlternatives($id));
    }

    // In case something else than ContainerInterface::NULL_ON_INVALID_REFERENCE
    // is used, the actual wanted behavior is to re-try getting the service at a
    // later point.
    if (!$definition) {
      return NULL;
    }

    // Definition is a keyed array, so [0] is only defined when it is a
    // serialized string.
    if (isset($definition[0])) {
      $definition = unserialize($definition);
    }

    // Now create the service.
    $this->loading[$id] = TRUE;

    try {
      $service = $this->createService($definition, $id);
    }
    catch (\Exception $e) {
      unset($this->loading[$id]);
      unset($this->services[$id]);

      if (ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE !== $invalid_behavior) {
        return NULL;
      }

      throw $e;
    }

    unset($this->loading[$id]);

    return $service;
  }

  /**
   * Resets shared services from the container.
   *
   * The container is not intended to be used again after being reset in a
   * normal workflow. This method is meant as a way to release references for
   * ref-counting. A subsequent call to ContainerInterface::get() will recreate
   * a new instance of the shared service.
   */
  public function reset() {
    $this->services = [];
  }

  /**
   * Creates a service from a service definition.
   *
   * @param array $definition
   *   The service definition to create a service from.
   * @param string $id
   *   The service identifier, necessary so it can be shared if its public.
   *
   * @return object
   *   The service described by the service definition.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   *   Thrown when the service is a synthetic service.
   * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
   *   Thrown when the configurator callable in $definition['configurator'] is
   *   not actually a callable.
   * @throws \ReflectionException
   *   Thrown when the service class takes more than 10 parameters to construct,
   *   and cannot be instantiated.
   */
  protected function createService(array $definition, $id) {
    if (isset($definition['synthetic']) && $definition['synthetic'] === TRUE) {
      throw new RuntimeException(sprintf('You have requested a synthetic service ("%s"). The service container does not know how to construct this service. The service will need to be set before it is first used.', $id));
    }

    $arguments = [];
    if (isset($definition['arguments'])) {
      $arguments = $definition['arguments'];

      if ($arguments instanceof \stdClass) {
        $arguments = $this->resolveServicesAndParameters($arguments);
      }
    }

    if (isset($definition['file'])) {
      $file = $this->frozen ? $definition['file'] : current($this->resolveServicesAndParameters([$definition['file']]));
      require_once $file;
    }

    if (isset($definition['factory'])) {
      $factory = $definition['factory'];
      if (is_array($factory)) {
        $factory = $this->resolveServicesAndParameters([$factory[0], $factory[1]]);
      }
      elseif (!is_string($factory)) {
        throw new RuntimeException(sprintf('Cannot create service "%s" because of invalid factory', $id));
      }

      $service = call_user_func_array($factory, $arguments);
    }
    else {
      $class = $this->frozen ? $definition['class'] : current($this->resolveServicesAndParameters([$definition['class']]));
      if ($class === 'Drupal\surgery\Service\SurgeryEntityReportGenerator') {
        $a = 1;
      }
      $service = new $class(...$arguments);
    }

    if (!isset($definition['shared']) || $definition['shared'] !== FALSE) {
      $this->services[$id] = $service;
    }

    if (isset($definition['calls'])) {
      foreach ($definition['calls'] as $call) {
        $method = $call[0];
        $arguments = [];
        if (!empty($call[1])) {
          $arguments = $call[1];
          if ($arguments instanceof \stdClass) {
            $arguments = $this->resolveServicesAndParameters($arguments);
          }
        }
        call_user_func_array([$service, $method], $arguments);
      }
    }

    if (isset($definition['properties'])) {
      if ($definition['properties'] instanceof \stdClass) {
        $definition['properties'] = $this->resolveServicesAndParameters($definition['properties']);
      }
      foreach ($definition['properties'] as $key => $value) {
        $service->{$key} = $value;
      }
    }

    if (isset($definition['configurator'])) {
      $callable = $definition['configurator'];
      if (is_array($callable)) {
        $callable = $this->resolveServicesAndParameters($callable);
      }

      if (!is_callable($callable)) {
        throw new InvalidArgumentException(sprintf('The configurator for class "%s" is not a callable.', get_class($service)));
      }

      call_user_func($callable, $service);
    }

    return $service;
  }

  /**
   * {@inheritdoc}
   *
   * phpcs:ignore Drupal.Commenting.FunctionComment.VoidReturn
   * @return void
   */
  public function set($id, $service) {
    $this->services[$id] = $service;
  }

  /**
   * {@inheritdoc}
   */
  public function has($id): bool {
    return isset($this->aliases[$id]) || isset($this->services[$id]) || isset($this->serviceDefinitions[$id]) || $id === 'service_container';
  }

  /**
   * {@inheritdoc}
   */
  public function getParameter($name): array|bool|string|int|float|NULL {
    if (!\array_key_exists($name, $this->parameters)) {
      if (!$name) {
        throw new ParameterNotFoundException('');
      }

      throw new ParameterNotFoundException($name, NULL, NULL, NULL, $this->getParameterAlternatives($name));
    }

    return $this->parameters[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function hasParameter($name): bool {
    return \array_key_exists($name, $this->parameters);
  }

  /**
   * {@inheritdoc}
   *
   * phpcs:ignore Drupal.Commenting.FunctionComment.VoidReturn
   * @return void
   */
  public function setParameter($name, $value) {
    if ($this->frozen) {
      throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    $this->parameters[$name] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function initialized($id): bool {
    if (isset($this->aliases[$id])) {
      $id = $this->aliases[$id];
    }

    return \array_key_exists($id, $this->services);
  }

  /**
   * Resolves arguments that represent services or variables to the real values.
   *
   * @param array|object $arguments
   *   The arguments to resolve.
   *
   * @return array
   *   The resolved arguments.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   *   If a parameter/service could not be resolved.
   * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
   *   If an unknown type is met while resolving parameters and services.
   */
  protected function resolveServicesAndParameters($arguments) {
    // Check if this collection needs to be resolved.
    if ($arguments instanceof \stdClass) {
      if ($arguments->type !== 'collection') {
        throw new InvalidArgumentException(sprintf('Undefined type "%s" while resolving parameters and services.', $arguments->type));
      }
      $arguments = $arguments->value;
    }

    // Process the arguments.
    foreach ($arguments as $key => $argument) {
      // For this machine-optimized format, only \stdClass arguments are
      // processed and resolved. All other values are kept as is.
      if ($argument instanceof \stdClass) {
        $type = $argument->type;

        // Check for parameter.
        if ($type == 'parameter') {
          $name = $argument->name;
          if (!isset($this->parameters[$name])) {
            $arguments[$key] = $this->getParameter($name);
            // This can never be reached as getParameter() throws an Exception,
            // because we already checked that the parameter is not set above.
          }

          // Update argument.
          $argument = $arguments[$key] = $this->parameters[$name];

          // In case there is not a machine readable value (e.g. a service)
          // behind this resolved parameter, continue.
          if (!($argument instanceof \stdClass)) {
            continue;
          }

          // Fall through.
          $type = $argument->type;
        }

        // Create a service.
        if ($type == 'service') {
          $id = $argument->id;

          // Does the service already exist?
          if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
          }

          if (isset($this->services[$id])) {
            $arguments[$key] = $this->services[$id];
            continue;
          }

          // Return the service.
          $arguments[$key] = $this->get($id, $argument->invalidBehavior);

          continue;
        }
        // Create private service.
        elseif ($type == 'private_service') {
          $id = $argument->id;

          // Does the private service already exist.
          if (isset($this->privateServices[$id])) {
            $arguments[$key] = $this->privateServices[$id];
            continue;
          }

          // Create the private service.
          $arguments[$key] = $this->createService($argument->value, $id);
          if ($argument->shared) {
            $this->privateServices[$id] = $arguments[$key];
          }

          continue;
        }
        elseif ($type == 'service_closure') {
          $arguments[$key] = function () use ($argument) {
            return $this->get($argument->id, $argument->invalidBehavior);
          };

          continue;
        }
        elseif ($type == 'iterator') {
          $services = $argument->value;
          $arguments[$key] = new RewindableGenerator(function () use ($services) {
            foreach ($services as $key => $service) {
              yield $key => $this->resolveServicesAndParameters([$service])[0];
            }
          }, count($services));

          continue;
        }
        // Check for collection.
        elseif ($type == 'collection') {
          $arguments[$key] = $this->resolveServicesAndParameters($argument->value);

          continue;
        }
        elseif ($type == 'raw') {
          $arguments[$key] = $argument->value;

          continue;
        }

        if ($type !== NULL) {
          throw new InvalidArgumentException(sprintf('Undefined type "%s" while resolving parameters and services.', $type));
        }
      }
    }

    return $arguments;
  }

  /**
   * Provides alternatives for a given array and key.
   *
   * @param string $search_key
   *   The search key to get alternatives for.
   * @param array $keys
   *   The search space to search for alternatives in.
   *
   * @return string[]
   *   An array of strings with suitable alternatives.
   */
  protected function getAlternatives($search_key, array $keys) {
    $alternatives = [];
    foreach ($keys as $key) {
      $lev = levenshtein($search_key, $key);
      if ($lev <= strlen($search_key) / 3 || str_contains($key, $search_key)) {
        $alternatives[] = $key;
      }
    }

    return $alternatives;
  }

  /**
   * Provides alternatives in case a service was not found.
   *
   * @param string $id
   *   The service to get alternatives for.
   *
   * @return string[]
   *   An array of strings with suitable alternatives.
   */
  protected function getServiceAlternatives($id) {
    $all_service_keys = array_unique(array_merge(array_keys($this->services), array_keys($this->serviceDefinitions)));
    return $this->getAlternatives($id, $all_service_keys);
  }

  /**
   * Provides alternatives in case a parameter was not found.
   *
   * @param string $name
   *   The parameter to get alternatives for.
   *
   * @return string[]
   *   An array of strings with suitable alternatives.
   */
  protected function getParameterAlternatives($name) {
    return $this->getAlternatives($name, array_keys($this->parameters));
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceIds() {
    return array_merge(['service_container'], array_keys($this->serviceDefinitions + $this->services));
  }

  /**
   * Ensure that cloning doesn't work.
   */
  private function __clone() {
  }

}
