<?php

namespace Assegai\Cli\Schematics;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Exceptions\FileException;
use Assegai\Cli\Exceptions\NotFoundException;
use Assegai\Cli\Exceptions\SchematicException;
use Assegai\Cli\Interfaces\ISchematic;
use Assegai\Cli\Util\Files;
use Assegai\Cli\Util\Paths;

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
   * @param SchematicEngineHost $host
   */
  public function __construct(protected readonly SchematicEngineHost $host)
  {
    $this->templateEngine = new TemplateEngine();
  }

  /**
   * @return SchematicEngineHost
   */
  public function getHost(): SchematicEngineHost
  {
    return $this->host;
  }

  /**
   * @param string $className
   * @return ISchematic
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
   * @param array $schema
   * @param object $args
   * @param array $globalArgs
   * @return string
   */
  public function loadSchema(array $schema, object $args, array $globalArgs): string
  {
    $this->templateEngine->setSchema($schema);
    $this->templateEngine->setArgs($args);
    $this->templateEngine->setGlobalArgs($globalArgs);
    return Paths::join($schema['path'], 'Files');
  }

  /**
   * @param string $templatePath
   * @param string $outputPath
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
   * @param bool $verbose
   */
  public function setVerbose(bool $verbose): void
  {
    $this->verbose = $verbose;
    $this->templateEngine->setVerbose($verbose);
  }

  /**
   * @param string $message
   * @param bool $ignoreVerbosity
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
   * @param string $sourcePath
   * @param string $targetPath
   * @return void
   * @throws SchematicException
   */
  private function copyTemplateFiles(string $sourcePath, string $targetPath): void
  {
    $this->report(message: "Copying template files...");

    $command = file_exists($targetPath) ? "cp -r -T $sourcePath $targetPath" : "cp -r $sourcePath $targetPath";

    if (false === exec($command) )
    {
      throw new SchematicException("Could not copy files from \n$sourcePath to\n$targetPath");
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
        Files::renameFile(from: $activePath, to: $resolvedPath);

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
          Console::logUpdate($resolvedPathBasename, $bytes);
        }
        else
        {
          Console::logCreate($resolvedPathBasename, $bytes);
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
              ? "/({$indent}{$metaProp}:\s*\[)(.*,?)(\])/"
              : "/({$indent}{$metaProp}:\s*\[\n)(.*,?)(\n.*)/";
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
              ? "$1$2{$separator}{$newContent}$3"
              : "$1$2{$separator}{$indent}{$indent}{$newContent}$3";

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
              Console::logUpdate("$moduleBasename", $bytes);
            }
          }
        }
      }
    }
  }

  /**
   * @param string $message
   * @param bool $ignoreVerbosity
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
   * @param string|array $sourceCode
   * @param array $namespaces
   * @return string
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