<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Attributes\ValidateWorkspace;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\CommandArgument;
use Assegai\Cli\Core\CommandOption;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Enumerations\SchematicType;
use Assegai\Cli\Enumerations\ValueRequirementType;
use Assegai\Cli\Enumerations\ValueType;
use Assegai\Cli\Exceptions\FileException;
use Assegai\Cli\Exceptions\FileNotFoundException;
use Assegai\Cli\Exceptions\InvalidFileException;
use Assegai\Cli\Exceptions\NotFoundException;
use Assegai\Cli\Exceptions\SchematicException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Interfaces\IExecutionContext;
use Assegai\Cli\Schematics\AbstractSchematic;
use Assegai\Cli\Schematics\SchematicEngine;
use Assegai\Cli\Schematics\SchematicEngineHost;
use Assegai\Cli\Util\Text;
use Assegai\Cli\Util\Paths;

#[Command(
  name: 'generate',
  shortName: 'g',
  description: 'Generates and/or modifies files based on a schematic.',
  options: [
    new CommandOption(name: 'dry-run', alias: 'd', description: 'Report actions that would be taken without writing out results.'),
    new CommandOption(name: 'project', alias: 'p', type: ValueRequirementType::REQUIRED, description: 'Project in which to generate files.', valueType: ValueType::STRING),
    new CommandOption(name: 'type', alias: 't', type: ValueRequirementType::REQUIRED, description: 'An optional type suffix to supply when generating a class schematic.', valueType: ValueType::STRING),
  ],
  arguments: [
    new CommandArgument(name: 'schematic', isRequired: true, description: 'The collection schematic to run.', valueType: ValueType::ENUM, enum: SchematicType::class),
    new CommandArgument(name: 'name', description: 'The name of the new collection/element.', valueType: ValueType::STRING),
    new CommandArgument(name: 'path', description: 'The path to the new collection/element.', valueType: ValueType::STRING),
  ]
)]
class GenerateCommand extends AbstractCommand
{
  private ?SchematicEngine $schematicEngine = null;

  /**
   * Configure the command.
   *
   * @return void
   */
  public function configure(): void
  {
    parent::configure();

    $engineHost = new SchematicEngineHost();
    $this->schematicEngine = new SchematicEngine(host: $engineHost);
  }

  /**
   * @param IArgumentHost|IExecutionContext $context
   * @return int
   * @throws FileNotFoundException
   * @throws InvalidFileException
   * @throws NotFoundException
   * @throws SchematicException
   * @throws FileException
   */
  #[ValidateWorkspace]
  public function execute(IArgumentHost|IExecutionContext $context): int
  {
    $schematicsPath = Paths::getCliBaseDirectory() . '/src/Schematics';
    $collectionsFilename = Paths::join($schematicsPath, 'collection.php');

    if (! file_exists($collectionsFilename) )
    {
      throw new FileNotFoundException($collectionsFilename);
    }
    $collection = require($collectionsFilename);

    if (! is_array($collection) )
    {
      throw new InvalidFileException(
        filename: $collectionsFilename,
        reason: "Expected array but found " . gettype($collection)
      );
    }
    $schematicName = $this->args->schematic;
    $schematicsClass = $collection['schema'][$schematicName] ?? null;

    if (! class_exists($schematicsClass) )
    {
      throw new NotFoundException($schematicsClass);
    }

    /** @var AbstractSchematic $schematic */
    $schematic = $this->schematicEngine->get($schematicsClass);

    if (! isset($this->args->name) )
    {
      $this->args->name = $schematic->promptForProperty('name');
    }
    $this->args->name = match ($this->args->schematic) {
      'interface' => 'I' . Text::pascalize($this->args->name),
      default => Text::pascalize($this->args->name),
    };
    $this->args->singular = Text::getSingularForm($this->args->name);

    $projectSourcePath = Paths::getWorkingDirectory() . '/src' . match ($this->args->schematic) {
      'config' => '/assegai.json',
      default => ''
    };

    if (! isset($this->args->path) )
    {
      # Create the path from the name
      $this->args->path = match($this->args->schematic) {
        'entity' => Text::getPluralForm($this->args->name),
        SchematicType::CONTROLLER->value,
        SchematicType::MODULE->value,
        SchematicType::RESOURCE->value,
        SchematicType::SERVICE->value => $this->args->name,
        default => ''
      };
    }

    $rootOutputPath = Paths::join($projectSourcePath, Paths::pascalize($this->args->path ?? ''));
    $args = $this->args;
    unset($args->schematic);

    $globalArguments = array_slice($GLOBALS['argv'], 4);
    $templateFilePath = $this->schematicEngine->loadSchema($schematic->schema, $args, $globalArguments);
    $this->schematicEngine->build(templatePath: $templateFilePath, outputPath: $rootOutputPath);

    return Command::SUCCESS;
  }

  public function getHelp(): string
  {
    $output = parent::getHelp();

    $output .= sprintf("%s\nAvailable Schematics:%s" . PHP_EOL, Color::YELLOW, Color::RESET);

    $schematics = SchematicType::cases();

    foreach ($schematics as $schematic)
    {
      $output .= sprintf("  %-20s %s\n", $schematic->value, SchematicType::getDescription($schematic));
    }

    return $output;
  }
}