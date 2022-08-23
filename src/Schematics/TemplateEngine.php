<?php

namespace Assegai\Cli\Schematics;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Util\Paths;
use Assegai\Cli\Util\Text;
use stdClass;
use function PHPUnit\Framework\countOf;

/**
 *
 */
final class TemplateEngine
{
  /**
   * @var array|mixed
   */
  private array $properties = [];
  /**
   * @var object
   */
  private object $args;
  /**
   * @var string[]
   */
  private array $globalArgs = [];
  /**
   * @var bool
   */
  private bool $verbose = false;

  /**
   * @param array $schema
   */
  public function __construct(private array $schema = [])
  {
    $this->properties = $this->schema['properties'] ?? [];
    $this->args = new stdClass();
    $this->globalArgs = $GLOBALS['argv'];
  }

  /**
   * @param string $pathTemplate
   * @return string
   */
  public function resolvePath(string $pathTemplate): string
  {
    $this->report("Resolving path, $pathTemplate");
    $path = '';
    $matches = [];

    if (preg_match('/\w*__([\w]+)@?([\w]+)*@?([\w]+)*__/', $pathTemplate, $matches))
    {
      $path = $this->resolvePropertyMatch($matches);
    }

    $directory = dirname($pathTemplate);
    $base = basename($pathTemplate);
    $resolvedPath = preg_match('/^\w*__(.*)__/', $base)
      ? preg_replace('/^(.*)__(.*)__(.*)(\.template(\.[\w]{3})*)+$/', '$1' . $path . '$3$5', $base)
      : preg_replace('/^__(.*)__(.*)(\.template(\.[\w]{3})*)+$/', $path . '$2$4', $base);
    $resolvedPath = Paths::join($directory, $resolvedPath);

    $this->report("Resolved path, $resolvedPath");
    return $resolvedPath;
  }

  /**
   * @param string $contentTemplate
   * @param string $pattern
   * @return string
   */
  public function resolveContent(
    string $contentTemplate,
    string $pattern = '/%==\s?__(\w+)@?(\w+)*@?(\w+)*__\s?==%/'
  ): string
  {
    $this->report("Resolving content, $contentTemplate");
    $content = $contentTemplate;
    $matches = [];

    if (preg_match_all($pattern, $contentTemplate, $matches, PREG_SET_ORDER))
    {
      foreach ($matches as $match)
      {
        $resolvedMatch = $this->resolvePropertyMatch($match);
        $replacementPattern = '/'. $match[0] . '/';
        $content = preg_replace($replacementPattern, $resolvedMatch, $content);
      }
    }

    return $content;
  }

  /**
   * @return array
   */
  public function getSchema(): array
  {
    return $this->schema;
  }

  /**
   * @param array $schema
   */
  public function setSchema(array $schema): void
  {
    $this->validateSchema($schema);
    $this->schema = $schema;
  }

  /**
   * @return array
   */
  public function getProperties(): array
  {
    return $this->properties;
  }

  /**
   * @param object $args
   * @return void
   */
  public function setArgs(object $args): void
  {
    $this->args = $args;
  }

  /**
   * @return object
   */
  public function getArgs(): object
  {
    return $this->args;
  }

  /**
   * @param array $globalArgs
   * @return void
   */
  public function setGlobalArgs(array $globalArgs): void
  {
    $this->globalArgs = $globalArgs;
  }

  /**
   * @param bool $verbose
   */
  public function setVerbose(bool $verbose): void
  {
    $this->verbose = $verbose;
  }

  /**
   * @param string $word
   * @return string
   */
  public function pascalize(string $word): string
  {
    return Text::pascalize($word);
  }

  /**
   * @param string $word
   * @return string
   */
  public function camelize(string $word): string
  {
    return lcfirst($this->pascalize($word));
  }

  /**
   * @param string $word
   * @return string
   */
  public function snakeize(string $word): string
  {
    $output = preg_replace('/[-\s_]+/', '_', $word);;
    return strtolower($output);
  }

  /**
   * @param string $word
   * @return string
   */
  public function lowercase(string $word): string
  {
    return strtolower($word);
  }

  /**
   * @param string $word
   * @return string
   */
  public function uppercase(string $word): string
  {
    return strtoupper($word);
  }

  /**
   * @param string $word
   * @return string
   */
  public function singular(string $word): string
  {
    return Text::getSingularForm($word);
  }

  /**
   * @param string $word
   * @return string
   */
  public function plural(string $word): string
  {
    return Text::getPluralForm($word);
  }

  /**
   * @param string $path
   * @return string
   */
  public function namespacify(string $path): string
  {
    $path = Paths::pascalize($path);
    return str_replace(DIRECTORY_SEPARATOR, '\\', $path);
  }

  /**
   * @param string $name
   * @return string|null
   */
  public function getPropertyDefault(string $name): ?string
  {
    if (!$this->properties[$name])
    {
      return null;
    }

    $property = $this->properties[$name];

    if (!$property['default'])
    {
      return null;
    }

    $defaultValue = $property['default'];

    if (is_string($defaultValue))
    {
      return $defaultValue;
    }

    if (!isset($defaultValue['source']))
    {
      return $defaultValue;
    }

    return match ($defaultValue['source']) {
      'argv' => $defaultValue['index'] ? $this->globalArgs[$defaultValue['index']] : null,
      default => null
    };
  }

  /**
   * @param array $schema
   * @return void
   */
  private function validateSchema(array $schema): void
  {
    // TODO: Implement validateSchema()
    # Throw exceptions if something goes wrong
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
   * @param array $matches
   * @return string
   */
  public function resolvePropertyMatch(array $matches): string
  {
    $propName = '';
    $method = '';
    $totalMatches = count($matches);

    if ($totalMatches > 1)
    {
      $propName = $matches[1];
    }

    if ($totalMatches > 2)
    {
      /** @var string $method */
      $method = $matches[2];

      if (!method_exists($this, $method))
      {
        $method = '';
      }
    }

    return (empty($method)
      ? $this->getArgs()->$propName
      : call_user_func_array([$this, $method], [$this->getArgs()->$propName])) ?? '';
  }

  /**
   * @param string $sourceContent
   * @param string $property
   * @return bool
   */
  public function inlineMetaData(string $sourceContent, string $property): bool
  {
    $pattern = "/$property:\s*\[(.*)];?/";
    if (preg_match($pattern, $sourceContent))
    {
      return true;
    }

    return false;
  }
}