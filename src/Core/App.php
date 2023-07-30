<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Exceptions\ConsoleException;
use Assegai\Cli\Exceptions\InsufficientDetailsException;
use Assegai\Cli\Exceptions\NotFoundException;
use Assegai\Cli\Interfaces\IExecutable;
use Assegai\Cli\Util\Logger\Log;
use ReflectionClass;
use ReflectionException;

/**
 * This class represents the main application.
 */
final class App
{
  /**
   * An array of commands that have been registered with the application.
   * @var IExecutable[] $commands
   */
  protected array $commands = [];

  /**
   * An array of short names that have been registered for commands.
   * @var string[] $commands
   */
  protected array $shortNames = [];

  /**
   * An array of directories to search when looking for commands.
   * @var array
   */
  protected array $searchDirectories = [];

  /**
   * The context in which actions are being executed.
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
   * Sets the configuration for the app.
   * @param array|object $config An array or object containing the configuration values to set.
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
   * Runs the app.
   * @return void
   */
  public function run(): void
  {
    try
    {
      $commandName = $this->context->getActionName();

      if (empty($commandName))
      {
        $commandName = 'help';
      }

      /** @var AbstractCommand $command */
      $command = match(true) {
        isset($this->commands[$commandName]) => $this->commands[$commandName],
        isset($this->shortNames[$commandName]) => $this->commands[$this->shortNames[$commandName]],
        default => $this->commands['help']
      };
      $command->configure();
      if ($secondArg = $this->context->getArgsById(0))
      {
        if (in_array($secondArg, ['--help', '-h']))
        {
          $command->help();
        }
      }
      $command->parseArguments($this->context->getArgs());
      $commandReflection = new ReflectionClass($command::class);
      $executeMethod = $commandReflection->getMethod('execute');
      $attributes = $executeMethod->getAttributes();
      foreach ($attributes as $attribute)
      {
        $attribute->newInstance();
      }

      $result = $command->execute($this->context);

      if ($result !== Command::SUCCESS)
      {
        throw new ConsoleException("Error returned while executing $commandName - Err($result)");
      }
    }
    catch (ConsoleException|ReflectionException $e)
    {
      Console::error($e->getMessage());
    }
  }

  /**
   * Returns an array of the commands that have been registered with the app.
   * @return IExecutable[]
   */
  public function getCommands(): array
  {
    return $this->commands;
  }

  /**
   * Registers a new command with the app.
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
   * Registers many new commands at once.
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
   * De-registers a command with the app.
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
   * Determines whether the app has a particular command registered.
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