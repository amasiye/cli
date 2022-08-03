<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Exceptions\ConsoleExceptions;
use Assegai\Cli\Exceptions\InsufficientDetailsException;
use Assegai\Cli\Exceptions\NotFoundException;
use Assegai\Cli\Interfaces\IExecutable;

/**
 *
 */
final class App
{
  /**
   * @var IExecutable[] $commands
   */
  protected array $commands = [];

  /**
   * @var string[] $commands
   */
  protected array $shortNames = [];

  /**
   * @var array
   */
  protected array $searchDirectories = [];

  /**
   * @var ActionContext
   */
  protected ActionContext $context;

  /**
   * Constructs the app.
   */
  public final function __construct()
  {
    $this->context = ActionContext::getInstance(app: $this);

    set_exception_handler(function($e) {
      if ($e instanceof NotFoundException)
      {
        Console::error($e->getMessage());
      }
      else if ($e instanceof InsufficientDetailsException)
      {
        Console::error($e->getMessage());
      }
      else
      {
        Console::error($e->getMessage());
      }
    });
  }

  /**
   * @param array|object $config
   * @return $this
   */
  public function setConfig(array|object $config): App
  {
    foreach ($config as $prop => $value)
    {
      if (property_exists($this, $prop))
      {
        $this->$prop = $value;
      }
    }
    return $this;
  }

  /**
   * @return void
   */
  public function run(): void
  {
    try
    {
      $commandName = $this->context->getActionName();
      /** @var AbstractCommand $command */
      $command = $this->commands[$commandName] ?? $this->commands[$this->shortNames[$commandName]] ?? $this->commands['help'];
      $command->configure();
      if ($secondArg = $this->context->getArgsById(0))
      {
        if (in_array($secondArg, ['--help', '-h']))
        {
          $command->help();
        }
      }
      $command->parseArguments($this->context->getArgs());
      $command->execute($this->context);
    }
    catch (ConsoleExceptions $e)
    {
      Console::error($e->getMessage());
    }
  }

  /**
   * @return IExecutable[]
   */
  public function getCommands(): array
  {
    return $this->commands;
  }

  /**
   * @param AbstractCommand|IExecutable $command
   * @return App
   */
  public function add(AbstractCommand|IExecutable $command): App
  {
    if ($this->doesNotHaveCommand($command))
    {
      $this->commands[$command->name] = $command;
      if ($command->shortName)
      {
        $this->shortNames[$command->shortName] = $command->name;
      }
    }

    return $this;
  }

  /**
   * @param array<AbstractCommand|IExecutable> $commands
   * @return $this
   */
  public function addAll(array $commands): App
  {
    foreach ($commands as $command)
    {
      $this->add($command);
    }

    return $this;
  }

  /**
   * @param AbstractCommand|IExecutable $command
   * @return App
   */
  public function remove(AbstractCommand|IExecutable $command): App
  {
    if ($this->hasCommand($command))
    {
      $this->commands = array_filter($this->commands, function($value) use ($command) {
        return !$value->equals($command);
      });
    }

    return $this;
  }

  /**
   * @param AbstractCommand|IExecutable|string $command
   * @return bool
   */
  public function hasCommand(AbstractCommand|IExecutable|string $command): bool
  {
    if (is_string($command))
    {
      return key_exists($command, $this->commands) || in_array($command, $this->shortNames);
    }

    foreach ($this->commands as $value)
    {
      if ($value->equals($command))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * @param AbstractCommand|IExecutable $command
   * @return bool
   */
  public function doesNotHaveCommand(AbstractCommand|IExecutable $command): bool
  {
    return !$this->hasCommand($command);
  }
}