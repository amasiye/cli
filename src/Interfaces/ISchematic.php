<?php

namespace Assegai\Cli\Interfaces;

/**
 *
 */
interface ISchematic
{
  /**
   * @param object $options
   * @return void
   */
  public function build(object $options): void;

  /**
   * @param string $path
   * @param string $content
   * @return void
   */
  public function createFile(string $path, string $content): void;

  /**
   * @param string $path
   * @param string $content
   * @return void
   */
  public function writeFile(string $path, string $content): void;

  /**
   * @param string $path
   * @param string $to
   * @return void
   */
  public function renameFile(string $path, string $to): void;

  /**
   * @param string $path
   * @return void
   */
  public function deleteFile(string $path): void;

  /**
   * @param string $key
   * @return mixed
   */
  public function getProperty(string $key): mixed;

  /**
   * @param string $property
   * @return string|null
   */
  public function promptForProperty(string $property): ?string;
}
