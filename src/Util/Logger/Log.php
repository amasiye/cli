<?php

namespace Assegai\Cli\Util\Logger;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Log
{
  protected static ?Log $instance = null;
  /** @var Logger $logger  */
  protected LoggerInterface $logger;

  private final function __construct()
  {
    $this->logger = new Logger('Assegai CLI');
  }

  public static function getInstance(): self
  {
    if (!self::$instance)
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function setLogger(LoggerInterface $logger): void
  {
    $this->logger = $logger;
  }

  public function log(string $tag, string $message, array $context = []): void
  {
    $this->logger->log(level: LogLevel::NOTICE,message: "$tag: $message", context: $context);
  }

  public function debug(string $tag, string $message, array $context): void
  {
    $this->logger->debug(message: "$tag: $message", context: $context);
  }

  public function error(string $tag, string $message, array $context): void
  {
    $this->logger->error(message: "$tag: $message", context: $context);
  }
}