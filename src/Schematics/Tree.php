<?php

namespace Assegai\Cli\Schematics;

use Assegai\Cli\Util\Paths;

/**
 *
 */
class Tree
{
  /**
   * @param string $path
   * @param Tree|null $parent
   * @param Tree[] $content
   */
  public function __construct(
    public readonly string $path,
    public readonly ?Tree $parent = null,
    protected array|string|null $content = null
  )
  {
  }

  public function hydrate(): void
  {
    if (is_dir($this->path))
    {
      $templateFiles = array_slice(scandir($this->path), 2);

      foreach ($templateFiles as $file)
      {
        $child = new Tree(path: $file, parent: $this);

        if (is_dir(Paths::join($this->getCanonicalPath(), $file)))
        {
          $child->hydrate();
        }

        $this->add($child);
      }
    }
  }

  /**
   * @param string $path
   * @return Tree|null
   */
  public function getChild(string $path): ?Tree
  {
    foreach ($this->content as $child)
    {
      if ($child->path === $path)
      {
        return $child;
      }
    }

    return null;
  }

  /**
   * @param string $path
   * @return bool
   */
  public function childExists(string $path): bool
  {
    return !empty($this->getChild(path: $path));
  }

  /**
   * @param string $path
   * @return bool
   */
  public function childDoesNotExist(string $path): bool
  {
    return !$this->childExists($path);
  }

  /**
   * @param Tree|Tree[] $childOrChildren
   * @return void
   */
  public function add(Tree|array $childOrChildren): void
  {
    if ($childOrChildren instanceof Tree && $this->childDoesNotExist($childOrChildren->path))
    {
      $this->content[] = $childOrChildren;
    }

    if (is_array($childOrChildren))
    {
      foreach ($childOrChildren as $child)
      {
        $this->add($child);
      }
    }
  }

  /**
   * @return Tree[]
   */
  public function getContent(): array
  {
    return $this->content;
  }

  /**
   * @return int|false
   */
  public function commit(): int|false
  {
    # Write contents


    # Commit children
    return false;
  }

  /**
   * @param Tree[]|string|null $content
   */
  public function setContent(array|string|null $content): void
  {
    if (is_array($content))
    {
      $this->content = array_filter($content, fn($item) => $item instanceof Tree );
    }
    else
    {
      $this->content = $content;
    }
  }

  /**
   * @return string
   */
  public function getCanonicalPath(): string
  {
    if ($this->parent)
    {
      return $this->parent->getCanonicalPath() . "/$this->path";
    }

    return $this->path;
  }
}