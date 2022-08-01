<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\Console\Input\InputArgument;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Exceptions\ConsoleExceptions;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IComparable;
use Assegai\Cli\Interfaces\IExecutable;
use Assegai\Cli\Util\Logger\Log;
use Assegai\Cli\Util\Paths;
use Closure;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\Console\Input\InputOption;

#[Command(
  name: 'command',
  usage: 'command [command_name]',
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
   * @var string
   */
  public readonly string  $usage;
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

  protected int $lastArgumentIndex = 0;
  /** @var InputArgument[] $availableArguments  */

  protected array $availableArguments = [];

  /** @var CommandOption|InputOption[] $availableOptions */
  protected array $availableOptions = [];

  /** @var array $options */
  protected array $options = [];

  /** @var string[] $parsedArguments  */
  protected array $parsedArguments = [];

  /** @var string[] $arguments */
  protected array $arguments = [];

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
    }

    $this->availableOptions[] = new CommandOption(name: 'help', alias: 'h', type: ValueRequirementType::NOT_ALLOWED);
  }

  protected function getHeader(): string
  {
    $content = file_get_contents(sprintf("%s/header.txt", Paths::getResourceDirectory()));
    $output = Color::RED;
    $output .= $content;
    $output .= Color::RESET;
    return $output;
  }

  protected function printHeader(): void
  {
    echo $this->getHeader() . PHP_EOL;
  }

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
    $availableCommands = '';
    $availableOptions = '';

    return sprintf(
      "%s\n  USAGE: %s\n\n%s\n%s",
      $this->name,
      $this->usage,
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

  public function parseArguments(array $args)
  {
  }

  private function extractArguments(array $args): void
  {
  }

  private function extractOptions(array $args): void
  {
  }

  private function resolveOptions()
  {
  }

  private function bindArguments()
  {
  }

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

  protected function addArgument(
    string $name,
    int $mode = InputArgument::OPTIONAL,
    string $description = '',
    string|bool|int|float|array|null $default = null,
    Closure|array $suggestedValues = []
  ): self
  {
    $this->availableArguments[$name] =
      new InputArgument(
        name: $name,
        mode: $mode,
        description: $description,
        default: $default,
        suggestedValues: $suggestedValues
      );
    return $this;
  }

  public function getArgument(string $name): ?InputArgument
  {
    return null;
  }

  public function getOption(string $name): ?InputOption
  {
    return null;
  }
}