<?php

namespace Assegai\Cli\Core\Menus;

/**
 *
 */
class MenuOptions
{
  /**
   * @param bool|null $showDescriptions
   * @param bool $showIndexes
   * @param string $titleColor
   */
  public function __construct(
    private ?bool $showDescriptions = null,
    private bool $showIndexes = true,
    private string $titleColor = 'yellow'
  ) { }

  /**
   * @return bool|null
   */
  public function showDescriptions(): ?bool { return $this->showDescriptions; }

  /**
   * @return bool
   */
  public function showIndexes(): bool { return $this->showIndexes; }

  /**
   * @return string
   */
  public function titleColor(): string { return $this->titleColor; }

  /**
   * @param bool $showDescriptions
   * @return void
   */
  public function setShowDescriptions(bool $showDescriptions): void { $this->showDescriptions = $showDescriptions; }

  /**
   * @param bool $showIndexes
   * @return void
   */
  public function setShowIndexes(bool $showIndexes): void { $this->showIndexes = $showIndexes; }

  /**
   * @param string $titleColor
   * @return void
   */
  public function setTitleColor(string $titleColor): void { $this->titleColor = $titleColor; }
}