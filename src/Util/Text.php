<?php

namespace Assegai\Cli\Util;

use plejus\PhpPluralize\Inflector;

/**
 * The Text class represents character strings.
 */
class Text
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
   * @param Text $other
   * @return int Returns less than 0 if instance is less than other; > 0 if instance is greater than other,
   * and 0 if they are equal.
   */
  public function compareTo(Text $other): int
  {
    return strcmp($this->value, strval($other));
  }

  /**
   * Compares two strings lexicographically.
   * @param Text $other
   * @return int Returns less than 0 if instance is less than other; > 0 if instance is greater than other,
   * and 0 if they are equal.
   */
  public function compareToIgnoreCase(Text $other): int
  {
    return strcmp(strtolower($this->value), strtolower(strval($other)));
  }

  /**
   * Concatenates the specified string to the end of this string.
   * @param Text $other
   * @return Text
   */
  public function concat(Text $other): self
  {
    $value = $this->value . $other;

    return new self($value);
  }

  /**
   * @param Text $characterSequence
   * @return bool
   */
  public function contains(Text $characterSequence): bool
  {
    return str_contains($this->value, strval($characterSequence));
  }

  /**
   * Compares this string to the specified CharSequence.
   * @param Text $characterSequence
   * @return bool
   */
  public function equals(Text $characterSequence): bool
  {
    return $this->compareTo($characterSequence) === 0;
  }

  /**
   * Compares this string to the specified CharSequence.
   * @param Text $characterSequence
   * @return bool
   */
  public function equalsIgnoreCase(Text $characterSequence): bool
  {
    return $this->compareToIgnoreCase($characterSequence) === 0;
  }

  /**
   * @param Text|string $ch
   * @param int $fromIndex
   * @return int
   */
  public function indexOf(Text|string $ch, int $fromIndex = 0): int
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
   * @param string|Text $target
   * @param string|Text $replacement
   * @return Text
   */
  public function replace(string|Text $target, string|Text $replacement): self
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
   * @param string|Text $prefix
   * @param int $offset
   * @return bool
   */
  public function startsWith(string|Text $prefix, int $offset = 0): bool
  {
    $haystack = substr((string)$this, $offset);
    return str_starts_with($haystack, (string)$prefix);
  }

  /**
   * @param string|Text $suffix
   * @param int $offset
   * @return bool
   */
  public function endsWith(string|Text $suffix, int $offset = 0): bool
  {
    $haystack = substr((string)$this, $offset);
    return str_ends_with($haystack, (string)$suffix);
  }

  /**
   * @param int $beginIndex
   * @param int|null $length
   * @return Text|string
   */
  public function substring(int $beginIndex, ?int $length = null): Text|string
  {
    return substr((string)$this, $beginIndex, $length);
  }

  /**
   * @return Text
   */
  public function toLowerCase(): Text
  {
    return new Text(strtolower($this));
  }

  /**
   * @return Text
   */
  public function toUpperCase(): Text
  {
    return new Text(strtoupper($this));
  }

  /**
   * @param string $characters
   * @return Text
   */
  public function trim(string $characters = " \t\n\r\0\x0B"): Text
  {
    return new Text(trim($this, $characters));
  }

  /**
   * @param mixed $target
   * @return Text
   */
  public function valueOf(mixed $target): Text
  {
    return new Text(strval($target));
  }

  /**
   * @param string $format
   * @param ...$args
   * @return Text
   */
  public static function format(string $format, ...$args): Text
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
   * @param string $input
   * @return string
   */
  public static function kebabToSnakeCase(string $input): string
  {
    return str_replace('-', '_', $input);
  }

  /**
   * @param string $input
   * @return string
   */
  public static function kebabToCamelCase(string $input): string
  {
    $tokens = explode('-', $input);
    $output = '';

    foreach ($tokens as $index => $token)
    {
      $word = strtolower($token);
      if ($index !== 0)
      {
        $word = ucfirst($word);
      }

      $output .= $word;
    }

    return $output;
  }

  /**
   * @param string $input
   * @return string
   */
  public static function kebabToPascal(string $input): string
  {
    return ucfirst(self::kebabToCamelCase($input));
  }

  /**
   * @param string $word
   * @return string
   */
  public static function dasherize(string $word): string
  {
    $tokens = preg_split('/[\W_]/', $word);
    $output = [];

    foreach ($tokens as $token)
    {
      $output[] = strtolower($token);
    }

    return implode('-', $output);
  }

  /**
   * @param string $word
   * @return string
   */
  public static function snakerize(string $word): string
  {
    $tokens = preg_split('/\W/', $word);
    $output = [];

    foreach ($tokens as $token)
    {
      $output[] = strtolower($token);
    }

    return implode('_', $output);
  }

  /**
   * @param string $word
   * @return string
   */
  public static function pascalize(string $word): string
  {
    $tokens = preg_split('/[\W_]/', $word);
    $output = [];
    foreach ($tokens as $token)
    {
      $output[] = ucfirst($token);
    }

    return implode('', $output);
  }

  /**
   * @param string $word
   * @return string
   */
  public static function titleCase(string $word): string
  {
    $tokens = preg_split('/[\W_]/', $word);
    $output = [];
    foreach ($tokens as $token)
    {
      $output[] = ucfirst($token);
    }

    return implode(' ', $output);
  }

  /**
   * @param string $word
   * @return string
   */
  public static function sentenceCase(string $word): string
  {
    $tokens = preg_split('/[!.?]/', $word);
    $output = [];
    foreach ($tokens as $token)
    {
      $output[] = strtolower($token);
    }

    $output = implode(' ', $output);
    return ucfirst($output);
  }

  /**
   * @param string $word
   * @return string
   */
  public static function getPluralForm(string $word): string
  {
    $inflector = new Inflector();
    /* Suppress warning about alphanumeric delimiter in preg_match */
    return @$inflector->plural($word);
  }

  /**
   * @param string $word
   * @return string
   */
  public static function getSingularForm(string $word): string
  {
    $inflector = new Inflector();
    return @$inflector->singular($word);
  }

  /**
   * Determines whether a string of text ends with
   * @param string $text
   * @return bool
   */
  public static function endsWithPunctuation(string $text): bool
  {
    return (bool)preg_match('/[!?.]$/', $text);
  }

  /**
   * Adds terminal punctuation to given text.
   * @param string|null $text
   * @param string $punctuation
   * @return string
   */
  public static function terminate(string|null $text, string $punctuation = '.'): string
  {
    if (is_null($text))
    {
      return '';
    }

    if (!in_array($punctuation, ['.', '!', '?']))
    {
      $punctuation = '.';
    }

    return self::endsWithPunctuation($text) ? $text : "$text{$punctuation}";
  }
}