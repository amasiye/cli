<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Exceptions\ConsoleExceptions;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IExecutionContext;

/**
 * Command to start a local development server.
 *
 * @author Andrew Masiye <amasiye313@mgail.com>
 * @since 1.0.0
 * @version 2.0.0
 */
#[Command(
  name: 'serve',
  shortName: 's',
  description: 'Starts a local development server.',
  options: [
    new CommandOption(name: 'host', type: ValueRequirementType::REQUIRED, description: 'Host to listen on.', valueType: ValueType::STRING, defaultValue: 'localhost'),
    new CommandOption(name: 'open', alias: 'o', type: ValueRequirementType::NOT_ALLOWED, description: 'Opens the url in default browser.', valueType: ValueType::BOOLEAN, defaultValue: false),
    new CommandOption(name: 'port', alias: 'p', type: ValueRequirementType::REQUIRED, description: 'Port to listen on.', valueType: ValueType::INTEGER, defaultValue: 5000),
  ],
  arguments: [
    new CommandArgument(name: 'directory', description: 'The specific document root directory.', valueType: ValueType::STRING, defaultValue: null)
  ]
)]
class ServeCommand extends AbstractCommand
{
  /**
   * @param IArgumentHost|IExecutionContext $context
   * @return int
   * @throws ConsoleExceptions
   */
  public function execute(IArgumentHost|IExecutionContext $context): int
  {
    // TODO: Implement execute() method.
    $port = 5000;

    $browser = match (true) {
      (bool)exec('which sensible-browser') => 'sensible-browser', # LINUX
      (bool)exec('which $BROWSER') => '$BROWSER', # LINUX
      (bool)exec('which xdg-open') => 'xdg-open', # LINUX
      (bool)exec('which gnome-open') => 'gnome-open', # LINUX
      (bool)exec('which explorer.exe') => 'explorer.exe', # WINDOWS
      (bool)exec('which open') => 'open', # MACOS
      default => ''
    };

    if (! $browser )
    {
      Console::warn('Could not detect which web browser to use.');
    }

    if (exec("$browser http://localhost:$port") === false)
    {
      throw new ConsoleExceptions(message: 'Browser exception');
    }

    return Command::SUCCESS;
  }
}