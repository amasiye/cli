<?php

namespace Assegai\Cli\Core;

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
   *
   */
  public final function __construct()
  {
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
    // Parse Arguments
    // Select handler
    // Execute
  }

  /**
   * @param AbstractCommand|IExecutable $command
   * @return int
   */
  public function add(AbstractCommand|IExecutable $command): int
  {
    if ($this->doesNotHave($command))
    {
      $this->commands[] = $command;
    }

    return count($this->commands);
  }

  /**
   * @param AbstractCommand|IExecutable $command
   * @return int
   */
  public function remove(AbstractCommand|IExecutable $command): int
  {
    if ($this->has($command))
    {
      $this->commands = array_filter($this->commands, function($value) use ($command) {
        return !$value->equals($command);
      });
    }

    return count($this->commands);
  }

  /**
   * @param AbstractCommand|IExecutable $command
   * @return bool
   */
  public function has(AbstractCommand|IExecutable $command): bool
  {
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
  public function doesNotHave(AbstractCommand|IExecutable $command): bool
  {
    return !$this->has($command);
  }
}