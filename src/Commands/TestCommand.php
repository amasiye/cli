<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Exceptions\TestException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IExecutionContext;
use Assegai\Cli\Util\Paths;

#[Command(
  name: 'test',
  shortName: 't',
  description: 'Runs or manages tests in the project.',
  options: [
    new CommandOption(name: 'dry-run', type: ValueRequirementType::NOT_ALLOWED, description: '', valueType: ValueType::BOOLEAN)
  ],
  arguments: [
    new CommandArgument(name: 'command', description: 'The name of the project to build. Can be an application or a library.', valueType: ValueType::STRING)
  ]
)]
class TestCommand extends AbstractCommand
{
  /**
   * @throws TestException
   */
  public function execute(IArgumentHost|IExecutionContext $context): int
  {
    $args = implode(' ', $context->getArgs());
    $codeception = Paths::join(Paths::getWorkingDirectory(), 'vendor/bin/codecept');
    $testResult = passthru("$codeception --ansi $args");

    if (false === $testResult)
    {
      throw new TestException();
    }

    Console::log(PHP_EOL . "Test Results:\n$testResult");

    return Command::SUCCESS;
  }

  public function getHelp(): string
  {
    $codeception = Paths::join(Paths::getWorkingDirectory(), 'vendor/bin/codecept');
    $result = passthru("$codeception help");

    if ($result !== false)
    {
      return '';
    }

    return parent::getHelp();
  }
}