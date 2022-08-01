<?php

namespace Assegai\Cli\Util;

use plejus\PhpPluralize\Inflector;

/**
 * The String class represents character strings.
 */
class CharacterSequence
{
  /**
   * Initializes a newly created String object so that it represents the same sequence of characters
   * as the argument; in other words, the newly created string is a copy of the argument string.
   * Unless an explicit copy of original is needed, use of this constructor is unnecessary since
   * Strings are immutable.
   * @param string $value A string
   */
  public function __construct(protected string $value = '')
  {
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return $this->value;
  }

  /**
   * @return int
   */
  public function length(): int
  {
    return strlen($this->value);
  }

  /**
   * Returns the char value at the specified index.
   * @param int $index
   * @return string Returns the char value at the specified index.
   */
  public function charAt(int $index): string
  {
    return substr($this->value, $index, 1);
  }

  /**
   * Compares two strings lexicographically.
   * @param CharacterSequence $other
   * @return int Returns less than 0 if instance is less than other; > 0 if instance is greater than other,
   * and 0 if they are equal.
   */
  public function compareTo(CharacterSequence $other): int
  {
    return strcmp($this->value, strval($other));
  }

  /**
   * Compares two strings lexicographically.
   * @param CharacterSequence $other
   * @return int Returns less than 0 if instance is less than other; > 0 if instance is greater than other,
   * and 0 if they are equal.
   */
  public function compareToIgnoreCase(CharacterSequence $other): int
  {
    return strcmp(strtolower($this->value), strtolower(strval($other)));
  }

  /**
   * Concatenates the specified string to the end of this string.
   * @param CharacterSequence $other
   * @return CharacterSequence
   */
  public function concat(CharacterSequence $other): self
  {
    $value = $this->value . $other;

    return new self($value);
  }

  /**
   * @param CharacterSequence $characterSequence
   * @return bool
   */
  public function contains(CharacterSequence $characterSequence): bool
  {
    return str_contains($this->value, strval($characterSequence));
  }

  /**
   * Compares this string to the specified CharSequence.
   * @param CharacterSequence $characterSequence
   * @return bool
   */
  public function equals(CharacterSequence $characterSequence): bool
  {
    return $this->compareTo($characterSequence) === 0;
  }

  /**
   * Compares this string to the specified CharSequence.
   * @param CharacterSequence $characterSequence
   * @return bool
   */
  public function equalsIgnoreCase(CharacterSequence $characterSequence): bool
  {
    return $this->compareToIgnoreCase($characterSequence) === 0;
  }

  /**
   * @param CharacterSequence|string $ch
   * @param int $fromIndex
   * @return int
   */
  public function indexOf(CharacterSequence|string $ch, int $fromIndex = 0): int
  {
    return strpos($this->value, $ch, $fromIndex);
  }

  /**
   * @return bool
   */
  public function isEmpty(): bool
  {
    return empty($this->value);
  }

  /**
   * @return bool
   */
  public function isBlank(): bool
  {
    return ($this->isEmpty() || preg_match('/^\s+$/', $this->value) > 0);
  }

  /**
   * @param int $ch
   * @param int $fromIndex
   * @return int
   */
  public function lastIndexOf(int $ch, int $fromIndex = 0): int
  {
    return strrpos($this->value, $ch, $fromIndex);
  }

  /**
   * Replaces each subsequence of this CharacterSequence that matches the literal target sequence with the
   * specified literal replacement sequence.
   * @return CharacterSequence
   */
  public function replace(string|CharacterSequence $target, string|CharacterSequence $replacement): self
  {
    $result = str_replace((string)$target, (string)$replacement, (string)$this);
    return new self($result);
  }

  /**
   * @param string|string[] $regex
   * @param string|string[] $replacement
   * @return $this
   */
  public function replaceAll(string|array $regex, string|array $replacement): self
  {
    $result = preg_replace($regex, $replacement, (string)$this);
    return new self($result);
  }

  /**
   * @param string $regex
   * @param int $limit
   * @param int $flags
   * @return string[]
   */
  public function split(string $regex, int $limit = -1, int $flags = 0): array
  {
    return preg_split($regex, (string)$this, $limit, $flags);
  }

  /**
   * @param string|CharacterSequence $prefix
   * @param int $offset
   * @return bool
   */
  public function startsWith(string|CharacterSequence $prefix, int $offset = 0): bool
  {
    $haystack = substr((string)$this, $offset);
    return str_starts_with($haystack, (string)$prefix);
  }

  /**
   * @param string|CharacterSequence $suffix
   * @param int $offset
   * @return bool
   */
  public function endsWith(string|CharacterSequence $suffix, int $offset = 0): bool
  {
    $haystack = substr((string)$this, $offset);
    return str_ends_with($haystack, (string)$suffix);
  }

  /**
   * @param int $beginIndex
   * @param int|null $length
   * @return CharacterSequence|string
   */
  public function substring(int $beginIndex, ?int $length = null): CharacterSequence|string
  {
    return substr((string)$this, $beginIndex, $length);
  }

  /**
   * @return CharacterSequence
   */
  public function toLowerCase(): CharacterSequence
  {
    return new CharacterSequence(strtolower($this));
  }

  /**
   * @return CharacterSequence
   */
  public function toUpperCase(): CharacterSequence
  {
    return new CharacterSequence(strtoupper($this));
  }

  /**
   * @param string $characters
   * @return CharacterSequence
   */
  public function trim(string $characters = " \t\n\r\0\x0B"): CharacterSequence
  {
    return new CharacterSequence(trim($this, $characters));
  }

  /**
   * @param mixed $target
   * @return CharacterSequence
   */
  public function valueOf(mixed $target): CharacterSequence
  {
    return new CharacterSequence(strval($target));
  }

  /**
   * @param string $format
   * @param ...$args
   * @return CharacterSequence
   */
  public static function format(string $format, ...$args): CharacterSequence
  {
    return call_user_func_array('sprintf', [$format, ...$args]);
  }

  /**
   * @param string $input
   * @return string
   */
  public static function camelCaseToSnakeCase(string $input): string
  {
    $length = strlen($input);
    $output = '';
    $word = '';
    $tokens = [];

    for ($x = 0; $x < $length; $x++)
    {
      $ch = substr($input, $x, 1);

      if (ctype_upper($ch))
      {
        $tokens[] = $word;
        $word = '';
      }

      $word .= $ch;
    }

    $tokens[] = $word;
    $output = implode('_', $tokens);

    return strtolower($output);
  }

  /**
   * @param string $input
   * @return string
   */
  public static function snakeCaseToCamelCase(string $input): string
  {
    $replacement = str_replace('_', ' ', $input);
    $buffer = ucwords($replacement);
    $output = str_replace(' ', '', $buffer);

    return lcfirst($output);
  }

  /**
   * @param string $input
   * @return string
   */
  public static function pascalCaseToSnakeCase(string $input): string
  {
    $output = self::pascalCaseToCamelCase(input: $input);

    return self::camelCaseToSnakeCase(input: $output);
  }

  /**
   * @param string $input
   * @return string
   */
  public static function snakeCaseToPascalCase(string $input): string
  {
    $tokens = explode('_', $input);

    $output = array_map(function ($token) {
      return strtoupper(substr($token, 0, 1)) . strtolower(substr($token, 1));
    }, $tokens);

    return implode($output);
  }

  /**
   * @param string $input
   * @return string
   */
  public static function pascalCaseToCamelCase(string $input): string
  {
    return lcfirst($input);
  }

  /**
   * @param string $input
   * @return string
   */
  public static function camelCaseToPascalCase(string $input): string
  {
    return ucfirst($input);
  }

  /**
   * @param string $word
   * @return string
   */
  public static function getPluralForm(string $word): string
  {
    $inflector = new Inflector();
    return $inflector->plural($word);
  }

  /**
   * @param string $word
   * @return string
   */
  public static function getSingularForm(string $word): string
  {
    $inflector = new Inflector();
    return $inflector->singular($word);
  }
}