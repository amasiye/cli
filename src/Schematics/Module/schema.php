<?php

return [
  'properties' => [
    'name' => [
      'description' => 'The name of the new module.',
      'type' => 'string',
      'pattern' => "^(?:@[a-zA-Z0-9-*~][a-zA-Z0-9-*._~]*/)?[a-zA-Z0-9-~][a-zA-Z0-9-._~]*$",
      'default' => [
        'source' => 'argv',
        'index' => 0,
      ],
      'x-prompt' => "What name would you like to use for the module?",
    ],
    'path' => [
      'description' => 'The path where the schema files will be generated.',
      'type' => 'string',
      'pattern' => "/^(\/?\w+\/?)+$/",
      'default' => [
        'source' => 'argv',
        'index' => 1,
      ]
    ],
    'updateModule' => [
      'path' => '/AppModule.php',
      'use' => ['Assegai\App\__name@pascalize__\__name@pascalize__Module'],
      'imports' => ['__name@pascalize__Module::class'],
    ]
  ]
];