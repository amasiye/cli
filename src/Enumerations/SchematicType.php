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
}