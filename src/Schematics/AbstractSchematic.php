<?php

namespace Assegai\Cli\Schematics;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Exceptions\NotFoundException;
use Assegai\Cli\Interfaces\ISchematic;
use Assegai\Cli\Util\Paths;
use ReflectionClass;

/**
 *
 */
abstract class AbstractSchematic implements ISchematic
{
  /**
   * @var array
   */
  public readonly array $schema;

  protected array $executables = [
    'x-analytics' => [],
    'x-prompt' => [],
  ];

  /**
   * @throws NotFoundException
   */
  public function __construct()
  {
    $reflection = new ReflectionClass(get_called_class());
    $schemaDirectory =
      Paths::join(__DIR__, str_replace('Schematic', '', $reflection->getShortName()));
    $schemaFilename = Paths::join($schemaDirectory, 'schema.php');


    if (! file_exists($schemaFilename) )
    {
      throw new NotFoundException($schemaFilename);
    }

    $schema = require($schemaFilename);
    $schema['path'] = $schemaDirectory;
    $this->schema = $schema;

    foreach ($this->schema['properties'] as $property => $fields)
    {
      foreach ($fields as $key => $value)
      {
        if (str_starts_with($key, 'x-'))
        {
          $this->executables[$key][$property] = $value;
        }
      }
    }
  }

  /* File methods */
  /**
   * @param string $path
   * @param string $content
   * @return void
   */
  public function createFile(string $path, string $content): void
  {
    if ($bytes = file_put_contents($path, $content))
    {
      Console::logCreate($path, $bytes);
    }
    else
    {
      Console::error("Could not create $path");
    }
  }

  /**
   * @param string $path
   * @param string $content
   * @return void
   */
  public function writeFile(string $path, string $content): void
  {
    if ($bytes = file_put_contents($path, $content))
    {
      Console::logUpdate($path, $bytes);
    }
    else
    {
      Console::error("Could not update $path");
    }
  }

  /**
   * @param string $path
   * @param string $to
   * @return void
   */
  public function renameFile(string $path, string $to): void
  {
    if ( rename($path, $to) )
    {
      Console::logRename(path: $path, to: $to);
    }
    else
    {
      Console::error("Could not rename $path to $to");
    }
  }

  /**
   * @param string $path
   * @return void
   */
  public function deleteFile(string $path): void
  {
    if (unlink($path))
    {
      Console::logDelete($path);
    }
    else
    {
      Console::error("Could not delete $path");
    }
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function getProperty(string $key): mixed
  {
    return $this->schema['properties'][$key] ?? null;
  }

  /**
   * @param string $property
   * @return string|null
   */
  public function promptForProperty(string $property): ?string
  {
    $message = $this->executables['x-prompt'][$property] ?? null;
    if (! $message )
    {
      return null;
    }

    return Console::prompt($message);
  }
}