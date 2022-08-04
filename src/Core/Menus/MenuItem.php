<?php

namespace Assegai\Cli\Core\Menus;

use Assegai\Cli\Enumerations\Color\Color;

class MenuItem
{
  public function __construct(
    private readonly string  $value,
    private readonly string  $description = '',
    private ?string          $index = null,
    private readonly string  $indexColor = 'blue',
    private readonly ?string $alias = null,
    private readonly ?string $fullDescription = null,
    private ?MenuOptions     $options = null
  ) {
    if (is_null($this->options))
    {
      $this->options = new MenuOptions();
    }
  }

  public function value(): string { return $this->value; }

  public function description(): string { return $this->description; }

  public function index(): ?string { return $this->index; }

  public function setIndex(string $index): void { $this->index = $index; }

  public function alias(): ?string { return $this->alias; }

  public function fullDescription(): ?string { return $this->fullDescription; }

  public function options(): ?MenuOptions { return $this->options; }

  public function __toString(): string
  {
    $color = strtolower($this->indexColor);
    $indexColorCode = match($color) {
      'black'   => Color::BLACK,
      'red'     => Color::LIGHT_RED,
      'green'   => Color::LIGHT_GREEN,
      'yellow'  => Color::LIGHT_YELLOW,
      'magenta' => Color::LIGHT_MAGENTA,
      'cyan'    => Color::LIGHT_CYAN,
      'white'   => Color::LIGHT_WHITE,
      default   => Color::LIGHT_BLUE
    };

    $output = '';

    if ($this->options()->showIndexes())
    {
      $output .= "$indexColorCode" . $this->index . "\e[0m) ";
    }

    $alias = is_null($this->alias()) ? '' : ' (' . $this->alias() . ')';

    return sprintf("%-2s%s%s", $output, $this->value, $alias);
  }

  public function display(?bool $withDescriptions = null): string
  {
    if (!is_null($this->options()->showDescriptions()))
    {
      $withDescriptions = $this->options()->showDescriptions();
    }

    return $withDescriptions
      ? sprintf("\e[1;34m%-18s\e[0m%s", strval($this), $this->description())
      : sprintf("\e[1;34m%s\e[0m", strval($this));
  }

  public function print_display(?bool $withDescriptions = null): void
  {
    echo $this->display(withDescriptions: $withDescriptions);
  }

  public function getHelp(): string
  {
    return 'Not Implemented';
  }

  public function help(): void
  {
    echo $this->getHelp();
  }
}