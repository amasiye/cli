<?php

namespace Assegai\Cli\Util\Logger;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Defines a logger for the CLI.
 */
class Log
{
  /** @var Log|null $instance */
  protected static ?Log $instance = null;
  /** @var Logger $logger  */
  protected LoggerInterface $logger;

  /**
   * Constructs the logger.
   */
  private final function __construct()
  {
    $this->logger = new Logger('Assegai CLI');
  }

  /**
   * Gets the singleton instance of the logger.
   *
   * @return Log
   */
  public static function getInstance(): self
  {
    if (!self::$instance)
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Sets the logger to use.
   *
   * @param LoggerInterface $logger
   */
  public function setLogger(LoggerInterface $logger): void
  {
    $this->logger = $logger;
  }

  /**
   * Logs a message at the info level.
   *
   * @param string $tag
   * @param string $message
   * @param array $context
   */
  public function log(string $tag, string $message, array $context = []): void
  {
    $this->logger->log(level: LogLevel::NOTICE,message: "$tag: $message", context: $context);
  }

  /**
   * Logs a message at the debug level.
   *
   * @param string $tag
   * @param string $message
   * @param array $context
   */
  public function debug(string $tag, string $message, array $context = []): void
  {
    $this->logger->debug(message: "$tag: $message", context: $context);
  }

  /**
   * Logs a message at the error level.
   *
   * @param string $tag
   * @param string $message
   * @param array $context
   */
  public function error(string $tag, string $message, array $context = []): void
  {
    $this->logger->error(message: "$tag: $message", context: $context);
  }
}