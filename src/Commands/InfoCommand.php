<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Exceptions\ConsoleExceptions;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Util\Paths;

#[Command(name: 'info', usage: 'assegai info|i', shortName: 'i', description: 'Display Assegai project details.')]
class InfoCommand extends AbstractCommand
{
  /**
   * @param IArgumentHost $context
   * @return int
   * @throws ConsoleExceptions
   */
  public function execute(IArgumentHost $context): int
  {
    $systemInfo = [
      'OS Version'        => PHP_OS_FAMILY,
      'PHP Version'       => PHP_VERSION,
      'Composer Version'  => str_replace('Composer version ', '', exec('composer -V')),
    ];

    $assegaiCLI = [
      'Assegai CLI Version' => VersionCommand::getVersion(),
      'Assegai Version' => $this->getFrameworkVersion(),
    ];
    $this->printHeader();

    printf("\n%s[System Information]%s\n", Color::GREEN, Color::RESET);
    foreach ($systemInfo as $key => $value)
    {
      printf("%-25s: %s%s%s\n", $key, Color::LIGHT_BLUE, $value, Color::RESET);
    }

    printf("\n%s[Assegai CLI]%s\n", Color::GREEN, Color::RESET);
    foreach ($assegaiCLI as $key => $value)
    {
      printf("%-25s: %s%s%s\n", $key, Color::LIGHT_BLUE, trim($value), Color::RESET);
    }

    if ($platformInfo = shell_exec('composer show | grep assegaiphp'))
    {
      printf("\n%s[Assegai Platform Information]%s\n", Color::GREEN, Color::RESET);
      echo $platformInfo . PHP_EOL;
    }

    return Command::SUCCESS;
  }

  /**
   * @return string
   */
  public function getFrameworkVersion(): string
  {
    $packageName = "assegaiphp/core";
    if (file_exists(Paths::getWorkingDirectory() . "/vendor/$packageName"))
    {
      return exec("composer show $packageName | grep versions") . "\n";
    }

    return "\n";
  }
}