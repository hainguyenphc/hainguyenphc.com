<?php

declare(strict_types = 1);

namespace Drupal\sm\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;

/**
 * Command to consume messages.
 */
#[AsCommand(name: 'messenger:consume', description: 'Consume messages')]
final class SmConsumeMessagesCommand extends ConsumeMessagesCommand {}
