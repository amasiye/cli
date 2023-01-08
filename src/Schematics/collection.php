<?php

use Assegai\Cli\Enumerations\SchematicType;
use Assegai\Cli\Util\Text;

function classifySchemaName($name): string
{
  $output = Text::pascalize($name);
  return sprintf("%sSchematic", $output);
}

$schematicTypes = SchematicType::cases();
$schema = [];

foreach ($schematicTypes as $type)
{
  $schematicTypeName = $type->value;

  if ($schematicTypeName === 'class')
  {
    $schematicTypeName = 'CustomClass';
  }

  $schema[$type->value] = 'Assegai\Cli\Schematics\\' . Text::pascalize($schematicTypeName) . '\\' . classifySchemaName($schematicTypeName);
}

return ['schema' => $schema];