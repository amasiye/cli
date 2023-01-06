<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Exceptions\ConsoleException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IExecutionContext;
use Assegai\Cli\Util\Config;
use Assegai\Cli\Util\Paths;

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
   * @throws ConsoleException
   */
  public function execute(IArgumentHost|IExecutionContext $context): int
  {
    $config = Config::get();
    $host = 'localhost';
    $port = '5000';
    $routerPath = "assegai-router.php";

    if (empty($config))
    {
      exit;
    }

    if (
      isset($config->development) &&
      isset($config->development->server)
    )
    {
      $server = $config->development->server;
      if (isset($server->host))
      {
        $host = $server->host;
      }

      if (isset($server->port))
      {
        $port = $server->port;
      }

      if (isset($server->router))
      {
        $routerPath = $server->router;
      }
    }

    $router = file_exists(Paths::getWorkingDirectory() . "/$routerPath") ? " $routerPath" : "";
    if (isset($this->options->host))
    {
      $host = $this->options->host;
    }

    if (isset($this->options->port))
    {
      $port = $this->options->port;
    }

    $command = sprintf("php -S %s:%s%s", $host, $port, $router);
    if (exec($command) === false)
    {
      throw new ConsoleException(message: 'Browser exception');
    }

    Console::log(message: sprintf("Starting Server...\n%sListening on port %s\n", Color::YELLOW, $port));

    $openBrowser = isset($this->options->open) ?? isset($config?->development?->openBrowser) ?? false;
    if ($openBrowser)
    {
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
    }

    return Command::SUCCESS;
  }
}