<?php

namespace Assegai\Cli\Schematics;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Exceptions\FileException;
use Assegai\Cli\Exceptions\NotFoundException;
use Assegai\Cli\Exceptions\SchematicException;
use Assegai\Cli\Interfaces\ISchematic;
use Assegai\Cli\Util\Directory;
use Assegai\Cli\Util\File;
use Assegai\Cli\Util\Paths;
use Phar;

/**
 * The SchematicEngine is responsible for loading and constructing `Collection`s and `Schematics`. When creating
 * an engine, the tooling provides a `SchematicEngineHost`.
 *
 * @version 1.0.0
 */
final class SchematicEngine
{
  /**
   * @var bool
   */
  private bool $verbose = false;
  /**
   * @var TemplateEngine
   */
  private TemplateEngine $templateEngine;

  /**
   * Constructs the engine.
   *
   * @param SchematicEngineHost $host
   */
  public function __construct(protected readonly SchematicEngineHost $host)
  {
    $this->templateEngine = new TemplateEngine();
  }

  /**
   * Returns the host.
   *
   * @return SchematicEngineHost The host.
   */
  public function getHost(): SchematicEngineHost
  {
    return $this->host;
  }

  /**
   * Returns the schematic of the given class name.
   *
   * @param string $className The class name.
   * @return ISchematic The schematic.
   * @throws NotFoundException
   */
  public function get(string $className): ISchematic
  {
    if (! is_subclass_of($className, ISchematic::class) )
    {
      throw new NotFoundException($className);
    }

    return new $className();
  }

  /**
   * Loads the schema.
   *
   * @param array $schema The schema.
   * @param object $args The arguments.
   * @param array $globalArgs The global arguments.
   * @return string The path to the template.
   */
  public function loadSchema(array $schema, object $args, array $globalArgs): string
  {
    $this->templateEngine->setSchema($schema);
    $this->templateEngine->setArgs($args);
    $this->templateEngine->setGlobalArgs($globalArgs);
    return Paths::join($schema['path'], 'Files');
  }

  /**
   * Builds the schematic.
   *
   * @param string $templatePath The path to the template.
   * @param string $outputPath The path to the output.
   * @return void
   * @throws FileException
   * @throws NotFoundException
   * @throws SchematicException
   */
  public function build(string $templatePath, string $outputPath): void
  {
    $this->report("Building frame...");
    $this->copyTemplateFiles(sourcePath: $templatePath, targetPath: $outputPath);
    $this->replacePlaceholders(targetPath: $outputPath);
    $this->cleanUpTemplateFiles(sourcePath: $templatePath, targetPath: $outputPath);
    $this->updateModuleMetaData(schema: $this->templateEngine->getSchema());
  }

  /**
   * @param array $tree
   * @return void
   */
  public function write(array $tree): void
  {
    $this->report("Committing changes...", ignoreVerbosity: true);
    var_export($tree);
  }

  /**
   * Sets the `SchematicEngine` verbosity.
   *
   * @param bool $verbose The verbosity.
   */
  public function setVerbose(bool $verbose): void
  {
    $this->verbose = $verbose;
    $this->templateEngine->setVerbose($verbose);
  }

  /**
   * Logs a message to the console. If the engine is not verbose, the message will be ignored.
   *
   * @param string $message The message.
   * @param bool $ignoreVerbosity Whether to ignore the verbosity.
   * @return void
   */
  private function report(string $message, bool $ignoreVerbosity = false): void
  {
    if ($ignoreVerbosity || $this->verbose)
    {
      Console::info($message);
    }
  }

  /**
   * Copies the template files to the output path.
   *
   * @param string $sourcePath The path to the template.
   * @param string $targetPath The path to the output.
   * @return void
   * @throws SchematicException
   */
  private function copyTemplateFiles(string $sourcePath, string $targetPath): void
  {
    $this->report(message: "Copying template files...");

    if (is_phar_path($sourcePath))
    {
      # Create a temporary directory
      $temporaryDirectory = Directory::createTemporary();

      # Extract the phar to the temporary directory
      printf("%sExtracting %s to %s%s" . PHP_EOL, Color::LIGHT_BLUE, $sourcePath, $temporaryDirectory, Color::RESET);

      # Copy the files from the temporary directory to the target path
      Directory::copy($temporaryDirectory, $targetPath);

      # Delete the temporary directory
      Directory::delete($temporaryDirectory);
      exit;
    }
    else
    {
      $command = file_exists($targetPath) ? "cp -r -T $sourcePath $targetPath" : "cp -r $sourcePath $targetPath";

      if (false === exec($command) )
      {
        throw new SchematicException("Could not copy files from \n$sourcePath to\n$targetPath");
      }
    }

    $this->log(message: sprintf("Source: %s%sTarget: %s" . PHP_EOL, $sourcePath, PHP_EOL, $targetPath));
  }

  /**
   * @param string $targetPath
   * @return void
   * @throws NotFoundException
   * @throws SchematicException
   * @throws FileException
   */
  private function replacePlaceholders(string $targetPath): void
  {
    $this->report(message: "Replacing placeholders...");

    if (! file_exists($targetPath) )
    {
      throw new NotFoundException("Target path $targetPath not found");
    }

    $files = array_slice(scandir($targetPath), 2);

    foreach ($files as $path)
    {
      $activePath = Paths::join($targetPath, $path);
      if (is_dir($activePath))
      {
        $this->replacePlaceholders($activePath);
      }
      else
      {
        # Resolve path names
        $resolvedPath = $this->templateEngine->resolvePath($activePath);
        $resolvedPathBasename = basename($resolvedPath);
        if (file_exists($resolvedPath))
        {
          continue;
        }
        $pathsAreEqual = strcmp($activePath, $resolvedPath) === 0;
        File::rename(from: $activePath, to: $resolvedPath);

        # Resolve content placeholders
        $content = file_get_contents($resolvedPath);
        if ($content === false)
        {
          throw new SchematicException("Could not read " . $resolvedPathBasename);
        }

        $resolvedContent = $this->templateEngine->resolveContent($content);
        $bytes = file_put_contents($resolvedPath, $resolvedContent);
        if ($bytes === false)
        {
          throw new SchematicException("Failed to write to $path");
        }

        if ($pathsAreEqual)
        {
          Console::logFileUpdate($resolvedPathBasename, $bytes);
        }
        else
        {
          Console::logFileCreate($resolvedPathBasename, $bytes);
        }
      }
    }

    $this->log(message: sprintf("Target: %s".PHP_EOL, $targetPath));
  }

  /**
   * @param string $sourcePath
   * @param string $targetPath
   * @return void
   * @throws SchematicException
   */
  private function cleanUpTemplateFiles(string $sourcePath, string $targetPath): void
  {
    $this->report(message: "Cleaning up...");

    if (is_dir($sourcePath))
    {
      $files = array_slice(scandir($sourcePath), 2);

      foreach ($files as $file)
      {
        $target = Paths::join($targetPath, $file);

        if (is_dir($target))
        {
          $source = Paths::join($sourcePath, $file);
          $this->cleanUpTemplateFiles($source, $target);
        }
        else if (file_exists($target))
        {
          if (false === exec("rm -r $target"))
          {
            throw new SchematicException("Failed to delete $file");
          }
        }
      }
    }
  }

  /**
   * @param array $schema
   * @return void
   */
  private function updateModuleMetaData(array $schema): void
  {
    if (isset($schema['properties']['updateModule']))
    {
      $updateInstructions = (object)$schema['properties']['updateModule'];

      # Resolve path names
      $resolvedPath = $this->templateEngine->resolvePath($updateInstructions->path);
      $modulePath = Paths::join(Paths::getWorkingDirectory(), 'src', $resolvedPath);

      if (file_exists($modulePath))
      {
        $content = file_get_contents($modulePath);
        // TODO: Use for loop to iterate over module meta props i.e. [imports, exports, providers, controllers]
        $metaProps = ['imports', 'exports', 'providers', 'controllers'];
        foreach ($metaProps as $metaProp)
        {
          $inlineMetaData = $this->templateEngine->inlineMetaData(sourceContent: $content, property: $metaProp);

          if (isset($updateInstructions->$metaProp) && $updateInstructions->$metaProp)
          {
            $indent = '  ';
            $pattern = $inlineMetaData
              ? "/($indent$metaProp:\s*\[)(.*,?)(\])/"
              : "/($indent$metaProp:\s*\[\n)(.*,?)(\n.*)/";
            $imports = [];

            foreach ($updateInstructions->$metaProp as $metaEntry)
            {
              if (preg_match_all('/__(\w+)+@?(\w+)*__/', $metaEntry, $matches, PREG_SET_ORDER))
              {
                foreach ($matches as $match)
                {
                  $propName = $match[1] ?? '';
                  if (!$propName)
                  {
                    continue;
                  }
                  $method = $match[2] ?? '';

                  $prop = $this->templateEngine->getArgs()->$propName;
                  if ($method && method_exists($this->templateEngine, $method))
                  {
                    $prop = $this->templateEngine->$method($prop);
                  }

                  $metaEntry = preg_replace('/' . $match[0] . '/', $prop, $metaEntry);
                }
              }

              if (!preg_match('/'.$metaEntry.'/', $content))
              {
                $imports[] = $metaEntry;
              }
            }

            $separator = $inlineMetaData ? ", " : "," . PHP_EOL;
            $newContent = implode($separator, $imports);
            if (!empty($newContent))
            {
              $newContent .= ',';
            }
            $replacement = $inlineMetaData
              ? "$1$2$separator$newContent$3"
              : "$1$2$separator$indent$indent$newContent$3";

            $resolvedContent = preg_replace($pattern, $replacement, $content);
            $resolvedContent = str_replace("[$separator", '[', $resolvedContent);
            $resolvedContent = str_replace("$separator]", ']', $resolvedContent);
            $resolvedContent = preg_replace('/,+/', ',', $resolvedContent);

            if ($inlineMetaData)
            {
              $resolvedContent = str_replace(',]', ']', $resolvedContent);
            }

            if (isset($updateInstructions->use))
            {
              $resolvedContent =
                $this->updateNamespaceUsageList(sourceCode: $resolvedContent, namespaces: $updateInstructions->use);
            }

            if ($bytes = file_put_contents($modulePath, $resolvedContent))
            {
              $moduleBasename = basename($modulePath);
              Console::logFileUpdate("$moduleBasename", $bytes);
            }
          }
        }
      }
    }
  }

  /**
   * Logs a message to the console if the verbose flag is set to true.
   *
   * @param string $message The message to log.
   * @param bool $ignoreVerbosity Whether to ignore the verbose flag.
   * @return void
   */
  private function log(string $message, bool $ignoreVerbosity = false): void
  {
    if ($this->verbose || $ignoreVerbosity)
    {
      Console::log($message);
    }
  }

  /**
   * Updates the namespace usage list in a module file.
   *
   * @param string|array $sourceCode The source code to update.
   * @param array $namespaces The namespaces to add to the usage list.
   * @return string The updated source code.
   */
  private function updateNamespaceUsageList(string|array $sourceCode, array $namespaces): string
  {
    $imports = '';

    foreach ($namespaces as $namespace)
    {
      if (preg_match('/__(\w*)@?(\w*)__/', $namespace, $matches))
      {
        $propName = $this->templateEngine->resolvePropertyMatch($matches);
        if ($propName)
        {
          $imports .= 'use ' .  str_replace($matches[0], $propName, $namespace) . ';' . PHP_EOL;
        }
      }
    }

    return preg_replace('/use(.*)\n\s(.*)(#\[Module)/', "use$1\n" . $imports . "\n$2$3", $sourceCode);
  }
}