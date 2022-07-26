<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Exceptions\ConsoleExceptions;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IComparable;
use Assegai\Cli\Interfaces\IExecutable;

/**
 *
 */
abstract class AbstractCommand implements IExecutable, IComparable
{
  /**
   * @var string
   */
  protected readonly string $id;

  /**
   * @param string $name
   * @param string $usage
   * @param string|null $shortName
   * @param string $description
   * @param string|null $longDescription
   */
  public final function __construct(
    public readonly string  $name,
    public readonly string  $usage,
    public readonly ?string $shortName = null,
    public readonly string  $description = '',
    public readonly ?string $longDescription = null,
  )
  {
    $this->id = uniqid('cmd-');
  }

  /**
   * @return string
   */
  public function getId(): string
  {
    return $this->id;
  }

  /**
   * @param AbstractCommand|IComparable $other
   * @return bool
   */
  public function equals(AbstractCommand|IComparable $other): bool
  {
    return $this->id === $other->getId();
  }

  /**
   * @param AbstractCommand|IComparable $other
   * @return int
   */
  public function compareTo(AbstractCommand|IComparable $other): int
  {
    return match(true) {
      $this->getId() > $other->getId() => 1,
      $this->getId() < $other->getId() => -1,
      default => 0
    };
  }

  /**
   * @return string
   */
  public function getHelp(): string
  {
    return '';
  }

  /**
   * @return void
   */
  public function help(): void
  {
    echo $this->getHelp();
  }

  /**
   * @param IArgumentHost $context
   * @return int
   * @throws ConsoleExceptions
   */
  public function undo(IArgumentHost $context): int
  {
    throw new ConsoleExceptions(sprintf("%s cannot be undone!", $this->name));
  }
}