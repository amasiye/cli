<?php

use Assegai\Cli\Enumerations\SchematicType;
use Assegai\Cli\Schematics\Module\ModuleSchematic;

return [
  'schema' => [
    SchematicType::MODULE->value => ModuleSchematic::class
  ]
];