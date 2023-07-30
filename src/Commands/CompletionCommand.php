<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Action;
use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Exceptions\NotFoundException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Util\Paths;
use Exception;

#[Command(
  name: 'completion',
  usage: 'assegai completion [argument]',
  description: 'Set up Assegai CLI autocompletion for your terminal.',
)]
class CompletionCommand extends AbstractCommand
{
  /**
   * @param IArgumentHost $context
   * @return int
   * @throws NotFoundException
   */
  public function execute(IArgumentHost $context): int
  {
    if (!empty($context->getArgs()))
    {
      try
      {
        $actionName = $context->getArgsById(0);
        if (!$this->canHandle($actionName))
        {
          throw new Exception("Unknown action. Did you mean script?");
        }
        return $this->handle($context->getArgsById(0), $context);
      }
      catch (Exception $exception)
      {
        exit($exception->getMessage());
      }
    }

    # 1. Check if the script is already installed
    $targetPath = '/usr/share/bash-completion/completions/assegai';
    if (file_exists($targetPath))
    {
      return Command::SUCCESS;
    }

    # 2. If not, create a new file and write the contents of our template file to it
    $filename = Paths::join(Paths::getWorkingDirectory(), 'assegai-completion.sh');
    $templatePath = Paths::join(Paths::getCliBaseDirectory(), 'src/assegai-completion.bash');
    $templateContent = file_get_contents($templatePath);

    if (!file_exists($templatePath))
    {
      throw new NotFoundException('Missing template file');
    }

    # 3. Move the file to the target path
    $bytesWritten = file_put_contents($filename, $templateContent);
    if (!$bytesWritten)
    {
      Console::error("Failed to create completion file.");
      return Command::ERROR_DEFAULT;
    }

    $username = exec('whoami');

    if (!$username)
    {
      Console::error("Could not retrieve current user name.");
      return Command::ERROR_DEFAULT;
    }

    $password = Console::promptPassword("[sudo] password for $username");

    $moveResult = shell_exec("echo $password | sudo -S mv $filename $targetPath");

    if (false === $moveResult)
    {
      Console::error("Failed to install completion file in $targetPath");
      return Command::ERROR_DEFAULT;
    }
    echo PHP_EOL;

    Console::logFileCreate(basename($filename), $bytesWritten);

    return Command::SUCCESS;
  }

  /**
   * @param IArgumentHost $host
   * @return int
   * @throws NotFoundException
   */
  #[Action(
    name: 'script',
    description: 'Generate a bash and zsh real-time type-ahead autocompletion script.'
  )]
  public function script(IArgumentHost $host): int
  {
    $completionScriptPath = Paths::join(Paths::getResourceDirectory(), 'completion.txt');

    if (!file_exists($completionScriptPath))
    {
      throw new NotFoundException($completionScriptPath);
    }

    echo file_get_contents($completionScriptPath);

    return Command::SUCCESS;
  }
}