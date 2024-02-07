**Symfony Messenger integration**

https://www.drupal.org/project/sm

[[_TOC_]]

# License

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

## Installation

### Requirements

 - Drupal 10.1 or later
 - Drupal core patches:
   - [Patch #1][patch-1]
   - [Patch #2][patch-2] (Already included in Drupal 11)

SM requires Composer to manage dependencies. The Drupal community
recommends [cweagans/composer-patches][cweagans-composer-patches] project to
manage and apply patches. See [Manage dependencies][manage-deps].

 [patch-1]: https://www.drupal.org/project/drupal/issues/2961380 "Support IteratorArgument in \Drupal\Component\DependencyInjection\Dumper\OptimizedPhpArrayDumper::dumpValue"
 [patch-2]: https://www.drupal.org/project/drupal/issues/3108020 "Support ServiceClosureArgument in \Drupal\Component\DependencyInjection\Dumper\OptimizedPhpArrayDumper::dumpValue"
 [manage-deps]: https://www.drupal.org/docs/develop/using-composer/manage-dependencies
 [cweagans-composer-patches]: https://github.com/cweagans/composer-patches

## Site builders

### Settings

The module is configured using container parameters, either directly with YAML
or with the config submodule.

#### Services YAML

The following is an example of possible configuration. Add this in a site-wide
services YAML referenced by `settings.php`.

```yaml
parameters:
  # These values are not flattened into a hierarchy under a top level `sm` key,
  # so they can be overridden individually.
  sm.routing:
    Drupal\my_module\MyMessage: mytransport
    Drupal\my_module\MyMessage2: [mytransport1, mytransport2]
    'Drupal\my_module\*': mytransport
    '*': mytransport

  # Set the default bus.
  # Value is the ID of the auto-generated service.
  sm.default_bus: sm.bus.my_bus

  sm.buses:
    # The key here is transformed into a service with this value as service ID.
    # i.e. `'sm.bus.' . $key`
    my_bus:
      # Set middleware IDs. If default_middleware is enabled below, then these
      # middlewares are inserted in between the _before_ and _after_ middleware.
      middleware: []
      # List of default middleware as found in SmCompilerPass.
      # Recommended to leave this enabled.
      default_middleware:
        enabled: true

  sm.transports:
    my_transport:
      dsn: 'doctrine://default'
      options: []
```

#### Settings via UI/Drupal Configuration

An optional _SM Config_ module is included for configuration via UI and
persistence with the Drupal configuration system. This submodule modifies
container parameters with the configuration on container rebuilds (cache
clears).

YAML and the submodule may be used together. Container configuration from the
submodule is additive (merged) to parameters provided by YAML;

Warning: uninstalling the module will remove configuration, which makes it
different to a traditional _UI_ modules like _Field UI_ or _Views UI_.

### Console application

#### Consume command

A consume command is included with SM.

```sh
# From the site root.
./vendor/bin/sm messenger:consume BUSNAMES
```

Replace BUSNAMES with one or more names (space separated) of
receivers/transports to consume in order of priority.

For example `doctrine` if utilising the
[doctrine transport][doctrine-transport]: `./vendor/bin/sm messenger:consume doctrine`

Consider running with `--memory-limit` and `--limit` options.

Since the command is designed to run indefinitely, consider combining with a
solution like [Supervisor][supervisor]. If site infrastructure does not
support long-lived commands, configure the command with server-side executed
cron and the `--time-limit` option.

For additional features or help, see
`sm messenger:consume --help`.

[prioritization]: https://symfony.com/doc/current/messenger.html#prioritized-transports "Transport prioritization"
[doctrine-transport]: https://www.drupal.org/project/sm_transport_doctrine "Drupal Symfony Messenger Doctrine transport"
[supervisor]: https://symfony.com/doc/current/messenger.html#supervisor-configuration

### Replace Core Queue with Symfony Messenger

SM is capable of intercepting legacy Drupal queue items and routing
them through the messenger bus. Existing plugin definitions and processing logic
operate just as they do normally.

Add to `settings.php`:

```php
# Add after module is enabled in Drupal!
$settings['queue_default'] = \Drupal\sm\QueueInterceptor\SmLegacyQueueFactory::class;
```

If there are other `queue_service_` or `queue_reliable_service_` prefixed
entries in `settings.php`, these will continue to override the default queue
factory. Remove these overrides or modify the factory to use the above class.

See `\Drupal\Core\Queue\QueueFactory::get` for more information on the mechanics
on the Drupal queue factory.

Since queue items will be routed into Symfony Messenger, ensure the consume
command is processing transports. Queue items will not be run via Drupal cron,
Web cron, or Drush queue commands. See _Consume command_ for how to run.

### Transports

Messages are processed immediately by default. Enable a _transport_ to send
messages such that they are stored before processing, just like legacy queues,
After a _transport_ is enabled, messages are configured to use specific
transports with either the `routing` parameter or _SM Config_.

#### Transports

Transports must be installed separately:

- [SQL database transport (via Doctrine)][doctrine-transport]
  A full-featured transport utilising the active Drupal database.
  Download and install the module as usual. The transport will be made available
  immediately with the `doctrine` alias. Multiple transports may be configured.

#### Defining transports

Some Transport projects bundle a default transport, however you may still want
to add additional transports, for example to [prioritize messages][prioritization].

Additional transports may be defined in a site-wide services YAML with the
`sm.transports` parameter. Keep in mind this will override the `synchronous`
transport, so add it back to retain it.

```yaml
parameters:
  sm.transports:
    synchronous:
      dsn: 'sync://'
    # Note: these use the 'doctrine' factory provided by sm_transport_doctrine.module.
    highpriority:
      dsn: 'doctrine://default?table_name=messenger_messages_high'
    lowpriority:
      dsn: 'doctrine://default?table_name=messenger_messages_low'
```

## Developers

### Creating a new message and message handler

There are only two requirements for a message. A message class, and a message
handler class.

A message must be serializable. Do not try to serialize services or Drupal
entities. If you need to reference either, use simple scalar identifiers.

Starter template:

```php
<?php

declare(strict_types = 1);

namespace Drupal\my_module;

final class MyMessage {

  public function __construct(public string $foo) {}

}
```

A message handler is a class residing in the src/Messenger directory of a
module. It has a simple structure, utilising the `#[AsMessageHandler]`
attribute, and the `__invoke` magic method.

Starter template:

```php
<?php

declare(strict_types = 1);

namespace Drupal\my_module\Messenger;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MyMessageHandler {

  public function __invoke(\Drupal\my_module\MyMessage $message): void {
    // Do something with $message.
    fwrite(\STDOUT, $message->foo)
  }

}
```

Replace `my_module` with a module name, `MyMessageHandler` with the class name,
and `MyMessage` with the message class name

For dependency injection, these message handlers have autowiring enabled.

Message handler class discovery is triggered with container rebuild
(`drush cr`).

**Alternative message handler definitions**

Message handlers can also use `#[AsMessageHandler]` attribute on any public
method, if you don't like the class-level attribute and `__invoke` style.

Message handlers may also be defined with `service.yml` files; which is useful
for _explicit_ dependency injection.

### Dispatch messages

Dispatching a message involves instantiation of the message and sending to a
bus:

### Default bus

If unsure which bus, try the default bus.

```php
<?php
/** @var \Symfony\Component\Messenger\MessageBusInterface $bus */
$bus = \Drupal::service('messenger.default_bus');
$message = new \Drupal\my_module\MyMessage(foo: 'bar');
$bus->dispatch($message);
```

### Specific bus

#### Dependency on bus

Add a dependency to the bus with a service.

```yaml
services:
  myservice:
    # ...
    arguments:
      - '@sm.bus.busname'
```

This method is only valid for compiled dependencies. i.e, not controllers and
`\Drupal::service(...)`.

#### Dependency on bus routing service

The _routable message bus_ service is available for dependency injection via
`messenger.routable_message_bus` service ID. It is not available for autowiring.

Replace `BUSNAME` with the desired bus ID.

```php
$message = new \Drupal\my_module\MyMessage(foo: 'bar');
$envelope = new Envelope($message, [
  new BusNameStamp('sm.bus.BUSNAME'),
]);
/** @var \Symfony\Component\Messenger\RoutableMessageBus $buses */
$buses = $this->routableMessageBus;
$buses->dispatch($envelope);
```

**Without dependency injection**

Lastly, you can add an alias to the service in a module if you need to access it
without dependency injection. Note: there is no compatibility promise with this
service ID.

```yaml
services:
  'my_module_routable_message_bus': '@messenger.routable_message_bus'
```

```php
/** @var \Symfony\Component\Messenger\RoutableMessageBus $buses */
$buses = \Drupal::service('my_module_routable_message_bus');
$envelope = new Envelope($message, [
  new BusNameStamp('sm.bus.BUSNAME'),
]);
$buses->dispatch($envelope);
```
