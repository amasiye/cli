<?php

namespace Assegai\App;

use Assegai\Core\Attributes\Modules\Module;
use Assegai\App\Posts\PostsModule;
use Assegai\App\Users\UsersModule;

#[Module(
  providers: [AppService::class],
  controllers: [AppController::class],
  imports: []
)]
class AppModule
{
}