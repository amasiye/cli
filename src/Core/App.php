<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Exceptions\InsufficientDetailsException;
use Assegai\Cli\Exceptions\InvalidOptionException;
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
        echo $e->getMessage();
      }
      else if ($e instanceof InsufficientDetailsException)
      {
        echo $e->getMessage();
      }
      else
      {
        echo $e->getMessage();
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
   * @throws InvalidOptionException
   */
  public function run(): void
  {
    $commandName = $this->context->getActionName();
    /** @var AbstractCommand $command */
    $command = $this->commands[$commandName] ?? $this->commands['help'];
    $command->configure();
    $command->parseArguments($this->context->getArgs());
    if ($secondArg = $this->context->getArgsById(0))
    {
      if (in_array($secondArg, ['--help', '-h']))
      {
        $command->help();
      }
    }
    $command->execute($this->context);
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
        $this->commands[$command->shortName] = $command;
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
      return key_exists($command, $this->commands);
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