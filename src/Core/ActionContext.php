<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Enumerations\ContextType;
use Assegai\Cli\Interfaces\IExecutionContext;

/**
 *
 */
class ActionContext implements IExecutionContext
{
  protected static ?ActionContext $instance = null;

  /**
   * @var array
   */
  protected array $args = [];
  protected string $actionName = '';

  private final function __construct(protected readonly App $app)
  {
    global $argv, $argc;
    if ($argc > 1)
    {
      $this->actionName = $argv[1];

      if ($argc > 2)
      {
        $this->args = array_slice($argv, 2);
      }
    }
  }

  public static function getInstance(App $app): ActionContext
  {
    if (!self::$instance)
    {
      self::$instance = new ActionContext(app: $app);
    }

    return self::$instance;
  }

  public function getActionName(): string
  {
    return $this->actionName;
  }

  /**
   * @return ContextType
   */
  public function getType(): ContextType
  {
    return ContextType::CONSOLE;
  }

  /**
   * @return array
   */
  public function getArgs(): array
  {
    return $this->args;
  }

  /**
   * @param int $id
   * @return mixed
   */
  public function getArgsById(int $id): mixed
  {
    return $this->args[$id] ?? null;
  }

  public function getApp(): App
  {
    return $this->app;
  }
}