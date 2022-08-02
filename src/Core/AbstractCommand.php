<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Exceptions\ConsoleExceptions;
use Assegai\Cli\Exceptions\InvalidOptionException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IComparable;
use Assegai\Cli\Interfaces\IExecutable;
use Assegai\Cli\Util\Logger\Log;
use Assegai\Cli\Util\Paths;
use ReflectionAttribute;
use ReflectionClass;

/**
 * The base Command class.
 */
#[Command(
  name: 'command',
  usage: 'command [options] [arguments]',
  description: 'The base command'
)]
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
   * @var string
   */
  public readonly string  $name;
  /**
   * @var string|null
   */
  public readonly ?string  $usage;
  /**
   * @var string|null
   */
  public readonly ?string $shortName;
  /**
   * @var string
   */
  public readonly string  $description;
  /**
   * @var string|null
   */
  public readonly ?string  $longDescription;

  /**
   * @var int
   */
  protected int $lastArgumentIndex = 0;

  /** @var CommandArgument[] $availableArguments  */
  protected array $availableArguments = [];

  /** @var CommandOption[] $availableOptions */
  protected array $availableOptions = [];

  /** @var array $options */
  protected array $options = [];

  /** @var string[] $parsedArguments  */
  protected array $parsedArguments = [];

  /** @var string[] $arguments */
  protected array $arguments = [];

  /**
   * @var Log
   */
  protected Log $logger;
  /**
   * @throws ConsoleExceptions
   */
  public final function __construct()
  {
    $this->logger = Log::getInstance();

    $this->id = uniqid('cmd-');

    $reflection = new ReflectionClass($this);
    /** @var ReflectionAttribute $commandAttribute */
    $commandAttributes = $reflection->getAttributes(Command::class);

    if (empty($commandAttributes))
    {
      throw new ConsoleExceptions('Command attribute not set for ' . $reflection->getName());
    }

    /** @var Command $commandAttributeInstance */
    foreach ($commandAttributes as $attribute)
    {
      $commandAttributeInstance = $attribute->newInstance();
      $this->name = $commandAttributeInstance->name;
      $this->usage = $commandAttributeInstance->usage;
      $this->shortName = $commandAttributeInstance->shortName;
      $this->description = $commandAttributeInstance->description;
      $this->longDescription = $commandAttributeInstance->longDescription;
      $this->availableArguments = $commandAttributeInstance->arguments;
      $this->availableOptions = $commandAttributeInstance->options;
    }

    $this->availableOptions[] = new CommandOption(
      name: 'help',
      alias: 'h',
      type: ValueRequirementType::NOT_ALLOWED,
      description: 'Outputs helpful information about this command.'
    );
  }

  /**
   * @return string
   */
  protected function getHeader(): string
  {
    $content = file_get_contents(sprintf("%s/header.txt", Paths::getResourceDirectory()));
    $output = Color::RED;
    $output .= $content;
    $output .= Color::RESET;
    return $output;
  }

  /**
   * @return void
   */
  protected function printHeader(): void
  {
    echo $this->getHeader() . PHP_EOL;
  }

  public function getUsage(): string
  {
    $usage = $this->name;
    if ($this->availableOptions)
    {
      $usage .= ' [options]';
    }

    foreach ($this->availableArguments as $argument)
    {
      $usage .= " [$argument->name]";
    }

    return !empty($this->usage) ? $this->usage : $usage;
  }

  /**
   * @return void
   */
  public function configure(): void
  {
    // TODO: Implement configure() method.
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
    $body = $this->longDescription ? sprintf("\n%s", $this->longDescription) : '';

    if ($this->availableArguments)
    {
      $body .= PHP_EOL . Color::YELLOW . "Available arguments:" . Color::RESET . PHP_EOL;
      /** @var CommandArgument $argument */
      foreach ($this->availableArguments as $argument)
      {
        $name = $argument->alias ? "$argument->name, $argument->alias" : $argument->name;
        $body .= sprintf("  %-20s %s" . PHP_EOL, $name, $argument->description);
      }
    }

    if ($this->availableOptions)
    {
      $body .= PHP_EOL . Color::YELLOW . "Available options:" . Color::RESET . PHP_EOL;
      /** @var CommandOption $option */
      foreach ($this->availableOptions as $option)
      {
        $name = $option->alias ? "$option->name, $option->alias" : $option->name;
        $description = $option->description;
        if (isset($option->defaultValue))
        {
          $description .= " Default: $option->defaultValue";
        }
        $body .= sprintf("  %-20s %s" . PHP_EOL, $name, $description);
      }
    }

    return sprintf(
      "%s\n  USAGE: %s\n\n%s\n%s",
      $this->name,
      $this->getUsage(),
      $this->description,
      $body
    );
  }

  /**
   * @return never
   */
  public function help(): never
  {
    echo $this->getHelp();
    exit;
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

  /**
   * @param array $args
   * @return void
   * @throws InvalidOptionException
   */
  public function parseArguments(array $args): void
  {
    $shortOptions = $this->getShortOptionsList();
    $longOptions = $this->getLongOptionsList();

    $totalArgs = count($args);
    for ($x = 0; $x < $totalArgs; $x++)
    {
      $token = $args[$x];
      if ($this->isShortOption($token))
      {
        $token = str_replace('-', '', $token);
        $option = $this->getOption($token);
        $option->setValue(false);

        if ($option->acceptsValue())
        {
          $nextIndex = $x + 1;
          $nextToken = $nextIndex < $totalArgs ? $args[$nextIndex] : null;

          if (! $nextToken && $option->type === ValueRequirementType::REQUIRED)
          {
            throw new InvalidOptionException(message: "$token requires a value.");
          }

          if ($this->isShortOption($nextToken))
          {
            throw new InvalidOptionException(message: "$token requires a value.");
          }

          if ($this->isLongOption($nextToken))
          {
            throw new InvalidOptionException(message: "$token requires a value.");
          }

          $option->setValue($nextToken);
        }
      }
    }
  }

  /**
   * @param array $args
   * @return void
   */
  private function extractArguments(array $args): void
  {
  }

  /**
   * @param array $args
   * @return void
   */
  private function extractOptions(array $args): void
  {
    // TODO: Implement extractOptions()
  }

  /**
   * @return void
   */
  private function resolveOptions(): void
  {
    // TODO: Implement resolveOptions()
  }

  /**
   * @return void
   */
  private function bindArguments(): void
  {
    // TODO: Implement bindArguments()
  }

  /**
   * @param array $args
   * @return string|null
   */
  protected function getNextArgument(array $args): ?string
  {
    $totalArgs = count($args);
    for ($x = $this->lastArgumentIndex; $x < $totalArgs; $x++)
    {
      $value = $args[$x];
      if (!str_starts_with($value, '-'))
      {
        $this->lastArgumentIndex = $x;
        return $value;
      }
    }

    return null;
  }

  /**
   * @param array $args
   * @return string|null
   */
  protected function getNextOptions(array $args): ?string
  {
    $totalArgs = count($args);
    for ($x = $this->lastArgumentIndex; $x < $totalArgs; $x++)
    {
      $value = $args[$x];
      if (str_starts_with($value, '-'))
      {
        $this->lastArgumentIndex = $x;
        return $value;
      }
    }

    return null;
  }

  /**
   * @param string $name
   * @param string|null $alias
   * @param ValueRequirementType $type
   * @return $this
   */
  public function addOption(
    string $name,
    ?string $alias = null,
    ValueRequirementType $type = ValueRequirementType::NOT_ALLOWED
  ): self
  {
    if (! $this->hasOption(name: $name, alias: $alias))
    {
      $this->availableOptions[$name] = new CommandOption(name: $name, alias: $alias, type: $type);
    }

    return $this;
  }

  public function addArgument(CommandArgument $argument): self
  {
    if (! $this->hasArgument(name: $argument->name, alias: $argument->alias) )
    {
      $this->availableArguments[$argument->name] = $argument;
      if ($argument->alias)
      {
        $this->availableArguments[$argument->alias] = $argument;
      }
    }

    return $this;
  }

  /**
   * @param string $name
   * @return CommandArgument|null
   */
  public function getArgument(string $name): ?CommandArgument
  {
    foreach ($this->availableArguments as $argument)
    {
      if ($argument->hasName($name))
      {
        return $argument;
      }
    }

    return null;
  }

  /**
   * @param string $name
   * @return CommandOption|null
   */
  public function getOption(string $name): ?CommandOption
  {
    foreach ($this->availableOptions as $option)
    {
      if ($option->hasName($name))
      {
        return $option;
      }
    }

    return null;
  }

  /**
   * @param string $name
   * @param string|null $alias
   * @return bool
   */
  private function hasOption(string $name, ?string $alias = null): bool
  {
    foreach ($this->availableOptions as $option)
    {
      if ($option->hasName($name) || $option->hasName($alias))
      {
        return true;
      }
    }
    return false;
  }

  /**
   * @param string $name
   * @param string|null $alias
   * @return bool
   */
  private function hasArgument(string $name, ?string $alias = null): bool
  {
    foreach ($this->availableArguments as $argument)
    {
      if ($argument->hasName($name) || $argument->hasName($alias))
      {
        return true;
      }
    }
    return false;
  }

  private function getShortOptionsList(): array
  {
    $options = [];

    foreach ($this->availableOptions as $option)
    {
      if ($option->alias)
      {
        $options[] = '-' . $option->alias;
      }
    }

    return $options;
  }

  private function getLongOptionsList(): array
  {
    return array_map(function ($argument) {
      return '--' . $argument->name;
    }, $this->availableArguments);
  }

  private function isShortOption(string $token): bool
  {
    return (bool)preg_match('/^-\w+/', $token);
  }

  private function isLongOption(string $token): bool
  {
    return (bool)preg_match('/^--\w+/', $token);
  }
}