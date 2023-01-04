<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Enumerations\Color\TextStyle;
use Assegai\Cli\Interfaces\IArgumentHost;

#[Command(
  name: 'update',
  usage: 'update [options]',
  shortName: 'u',
  description: 'Updates your application and its dependencies. See https://update.assegai.ml/'
)]
class UpdateCommand extends AbstractCommand
{

  public function execute(IArgumentHost $context): int
  {
    Console::print(message: sprintf(
      "%s%s▹▹▹▹▹%s Update in progress... ☕\n",
      Color::LIGHT_BLUE,
      TextStyle::BLINK->value,
      Color::RESET
    ));

    if (false === shell_exec("composer update"))
    {
      Console::error(obj: "Update error", exit: true);
    }

    Console::print(message: "\n✔️ Update complete! \n");

    return Command::SUCCESS;
  }
}