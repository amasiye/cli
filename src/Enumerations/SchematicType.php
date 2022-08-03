<?php

namespace Assegai\Cli\Enumerations;

use Assegai\Cli\Interfaces\IValuable;

/**
 *
 */
enum SchematicType: string implements IValuable
{
  case APPLICATION = 'application';
  case CUSTOM_CLASS = 'class';
  case CONFIG = 'config';
  case CONTROLLER = 'controller';
  case ATTRIBUTE = 'attribute';
  case FILTER = 'filter';
  case GUARD = 'guard';
  case INTERCEPTOR = 'interceptor';
  case INTERFACE = 'interface';
  case MIDDLEWARE = 'middleware';
  case MODULE = 'module';
  case PIPE = 'pipe';
  case PROVIDER = 'provider';
  case RESOLVER = 'resolver';
  case SERVICE = 'service';
  case LIBRARY = 'library';
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
}