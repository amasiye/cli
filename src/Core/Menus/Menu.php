<?php /** @noinspection DuplicatedCode */

namespace Assegai\Cli\Core\Menus;

use Assegai\Cli\Enumerations\Color\Color;
use Atatusoft\CLIMenus\IO\Input;

/**
 *
 */
class Menu
{
  /**
   * @var MenuItem|null
   */
  private ?MenuItem $selected = null;

  /**
   * @param array<int, MenuItem> $items
   */
  public function __construct(
    private string $title,
    private array $items = [],
    private ?MenuOptions $options = null,
    private ?string $description = null,
    private ?string $helpTip = null
  )
  {
    if (is_null($this->options))
    {
      $this->options = new MenuOptions();
    }

    if (!empty($items))
    {
      $buffer = $this->items;
      $this->items = [];

      $this->addRange(items: $buffer);
    }
  }

  /**
   * @return string
   */
  public function title(): string { return $this->title; }

  /**
   * @param string $title
   * @return void
   */
  public function setTitle(string $title): void { $this->title = $title; }

  /**
   * @return MenuOptions
   */
  public function options(): MenuOptions { return $this->options; }

  /**
   * @param MenuOptions $options
   * @return void
   */
  public function setOptions(MenuOptions $options): void { $this->options = $options; }

  /**
   * @return MenuItem|null
   */
  public function selected(): ?MenuItem { return $this->selected; }

  /**
   * @return string|null
   */
  public function description(): ?string { return $this->description; }

  /**
   * @param string $description
   * @return void
   */
  public function setDescription(string $description): void { $this->description = $description; }

  /**
   * @return string
   */
  public function helpTip(): string { return $this->helpTip; }

  /**
   * @param string $helpTip
   * @return void
   */
  public function setHelpTip(string $helpTip): void { $this->helpTip = $helpTip; }

  /**
   * @param string $index
   * @return bool
   */
  public function hasItem(string $index): bool
  {
    return key_exists(key: $index, array: $this->items);
  }

  /**
   * @param string $valueOrAlias
   * @return string|bool
   */
  public function getItemValue(string $valueOrAlias): string|bool
  {
    if ($this->hasItemWithValue(valueOrAlias: $valueOrAlias))
    {
      foreach ($this->items as $item)
      {
        if ($item->value() === $valueOrAlias || $item->alias() === $valueOrAlias)
        {
          return $item->value();
        }
      }
    }

    return false;
  }

  /**
   * @param string $valueOrAlias
   * @return bool
   */
  public function hasItemWithValue(string $valueOrAlias): bool
  {
    $hasItem = false;

    foreach ($this->items as $item)
    {
      if ($item->value() === $valueOrAlias || $item->alias() === $valueOrAlias)
      {
        $hasItem = true;
      }
    }

    return $hasItem;
  }

  /**
   * @param MenuItem $item
   * @return void
   */
  public function add(MenuItem $item): void
  {
    if (!key_exists($item->index(), $this->items))
    {
      $count = count($this->items) + 1;
      if (is_null($item->index()))
      {
        $item->setIndex(index: $count);
      }
      $this->items[$item->index()] = $item;
    }
    else
    {
      $errorMessage = 'WARNING: Duplicate MenuItem(' . $item->value() . ')';
      error_log(message: $errorMessage);
    }
  }

  /**
   * @param array $items
   * @return void
   */
  public function addRange(array $items): void
  {
    foreach ($items as $item)
    {
      if ($item instanceof MenuItem)
      {
        $this->add(item: $item);
      }
    }
  }

  /**
   * @param MenuItem|int $item
   * @return void
   */
  public function remove(MenuItem|int $item): void
  {
    $index = ($item instanceof MenuItem) ? $item->index() : $item;

    if (isset($this->items[$index]))
    {
      unset($this->items[$index]);
    }
  }

  /**
   * @param array $items
   * @return void
   */
  public function removeRange(array $items): void
  {
    foreach ($items as $item)
    {
      if ($item instanceof MenuItem || is_integer($item))
      {
        $this->remove(item: $item);
      }
    }
  }

  /**
   * @return void
   */
  public function clear(): void
  {
    $this->items = [];
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    $description = $this->options()->showDescriptions()
      ? $this->description() . "\n\n"
      : '';
    $titleColorCode = $this->getColorCode(color: $this->options()->titleColor());
    $itemsOutput = '';

    foreach ($this->items as $item)
    {
      $previousShowIndexes = $item->options()->showIndexes();

      if (!$this->options()->showIndexes())
      {
        $item->options()->setShowIndexes(false);
      }

      $itemsOutput .= $item->display(withDescriptions: $this->options->showDescriptions()) . "\n";

      $item->options()->setShowIndexes($previousShowIndexes);
    }

    return trim(sprintf("%s%s%s%s\n%s",
        $description, $titleColorCode, $this->title, Color::RESET, $itemsOutput)) . "\n";
  }

  /**
   * @return null|MenuItem|MenuItem[]
   */
  public function prompt(
    string $message = 'Choose option',
    bool $useKeypad = false,
    bool $multiSelect = false
  ): null|MenuItem|array
  {
    if ($useKeypad)
    {
      $options = [];

      foreach ($this->items as $item)
      {
        $options[] = $item->value();
      }

      $selectedIndex = 0;
      $response = Input::promptSelect(options: $options, message: $message, selectedIndex: $selectedIndex, multiSelect: $multiSelect);

      $this->selected = $this->items[($selectedIndex + 1)];

      if ($multiSelect)
      {
        echo Color::RESET;
        return $response;
      }
    }
    else
    {
      $inputColorCode = $this->getColorCode(color: 'blue');
      printf("%s\n%s:$inputColorCode ", $this, $message);
      $attemptsLeft = 4;
      $colorCode = $this->getColorCode(color: 'magenta');

      do
      {
        $choice = trim(fgets(STDIN));
        --$attemptsLeft;
        $isValidChoice = isset($this->items[$choice]);

        if ($isValidChoice)
        {
          $this->selected = $this->items[$choice];
        }
        else
        {
          if ($attemptsLeft <= 0)
          {
            $colorCode = $this->getColorCode(color: 'red');
            exit("\n${colorCode}Program terminating...\e[0m\n");
          }
          echo "\n${colorCode}Invalid choice. Try again!\n$attemptsLeft attempts left...\e[0m\n\n$message: $inputColorCode";
        }
      }
      while(!$isValidChoice);
    }

    echo Color::RESET;

    return $this->selected();
  }

  /**
   * @return string
   */
  public function getHelp(): string
  {
    $help = "Available options:\n";

    if (!is_null($this->description()))
    {
      $help .= sprintf("%s\n\n", $this->description());
    }
    else
    {
      $help .= "\n";
    }

    foreach ($this->items as $item)
    {
      $help .= sprintf("  %-10s%s\n", $item->value(), $item->description());
    }

    if (!is_null($this->helpTip()))
    {
      $help .= sprintf("\n%s\n", $this->helpTip());
    }

    return $help . "\n";
  }

  /**
   * @return void
   */
  public function help(): void
  {
    echo $this->getHelp();
  }

  /**
   * @param string $itemValueOrIndex
   * @return void
   */
  public function describeItem(string $itemValueOrIndex): void
  {
    foreach ($this->items as $index => $item)
    {
      if (in_array($itemValueOrIndex, [$index, $item->value(), $item->alias()]))
      {
        $commandColor = Color::BLUE;
        $titleColor   = Color::YELLOW;
        $colorReset   = Color::RESET;

        printf(
          "$commandColor%s$colorReset\n  %s\n\n${titleColor}Full Description:$colorReset\n%-2s%s\n",
          $item->value(),
          $item->description(),
          ' ',
          $item->fullDescription()
        );
        break;
      }
    }
  }

  /**
   * @param string $color
   * @return string
   */
  private function getColorCode(string $color): string
  {
    return match ($color) {
      'black'   => Color::BLACK,
      'red'     => Color::RED,
      'green'   => Color::GREEN,
      'yellow'  => Color::YELLOW,
      'blue'    => Color::BLUE,
      'magenta' => Color::MAGENTA,
      'cyan'    => Color::CYAN,
      'white'   => Color::WHITE,
      default   => Color::RESET
    };
  }
}