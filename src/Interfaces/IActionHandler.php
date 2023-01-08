<?php

namespace Assegai\Cli\Interfaces;

/**
 * Interface for handling actions in the command line interface.
 */
interface IActionHandler
{
  /**
   * Handles the given action in the provided execution context.
   * @param string $action The action to be handled.
   * @param IExecutionContext $context The execution context in which to handle the action.
   * @return int Returns the exit code of the action.
   */
  public function handle(string $action, IExecutionContext $context): int;

  /**
   * Determines whether this handler is capable of handling the given action.
   * @param string $action The action to be checked.
   * @return bool Returns true if the action can be handled, false otherwise.
   */
  public function canHandle(string $action): bool;
}