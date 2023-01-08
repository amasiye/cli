<?php

return [
  'properties' => [
    'name' => [
      'description' => 'The name of the new project.',
      'type' => 'string',
      'pattern' => "^(?:@[a-zA-Z0-9-*~][a-zA-Z0-9-*._~]*/)?[a-zA-Z0-9-~][a-zA-Z0-9-._~]*$",
      'default' => [
        'source' => 'argv',
        'index' => 0,
      ],
      'x-prompt' => 'What name would you like to use for the project (plural, e.g., "assegai-app")?',
    ]
  ]
];