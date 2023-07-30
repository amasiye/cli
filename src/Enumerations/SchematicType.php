<?php

namespace Assegai\Cli\Enumerations;

use Assegai\Cli\Interfaces\IValuable;

/**
 *
 */
enum SchematicType: string implements IValuable
{
  case APPLICATION = 'application';
  case ATTRIBUTE = 'attribute';
  case CUSTOM_CLASS = 'class';
  case CONFIG = 'config';
  case CONTROLLER = 'controller';
  case FILTER = 'filter';
  case GUARD = 'guard';
  case INTERCEPTOR = 'interceptor';
  case INTERFACE = 'interface';
  case MIDDLEWARE = 'middleware';
  case MODULE = 'module';
  case PIPE = 'pipe';
  case SERVICE = 'service';
  case RESOURCE = 'resource';

  /**
   * @return array
   */
  public static function values(): array
  {
    $values = [];

    foreach (self::cases() as $case)
    {
      $values[] = $case->value;
    }

    return $values;
  }

  public static function getDescription(SchematicType $type): string
  {
    return match ($type) {
      self::APPLICATION => 'Generate a new application workspace',
      self::ATTRIBUTE => 'Generate a custom attribute',
      self::CUSTOM_CLASS => 'Generate a new class',
      self::CONFIG => 'Generate a CLI configuration file',
      self::CONTROLLER => 'Generate a controller declaration',
      self::FILTER => 'Generate a filter declaration',
      self::GUARD => 'Generate a guard declaration',
      self::INTERCEPTOR => 'Generate an interceptor declaration',
      self::INTERFACE => 'Generate an interface',
      self::MIDDLEWARE => 'Generate a middleware declaration',
      self::MODULE => 'Generate a module declaration',
      self::PIPE => 'Generate a pipe declaration',
      self::SERVICE => 'Generate a service declaration',
      self::RESOURCE => 'Generate a new CRUD resource',
      default => ''
    };
  }
}