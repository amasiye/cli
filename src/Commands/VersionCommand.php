<?php

namespace Assegai\Cli\Commands;

use Assegai\Cli\Attributes\Command;
use Assegai\Cli\Core\AbstractCommand;
use Assegai\Cli\Core\App;
use Assegai\Cli\Exceptions\ConsoleException;
use Assegai\Cli\Interfaces\IArgumentHost;
use Assegai\Cli\Util\Logger\Log;
use Assegai\Cli\Util\Paths;

#[Command(
  name: 'version',
  usage: 'version',
  shortName: 'v',
  description: 'Output the current version.'
)]
class VersionCommand extends AbstractCommand
{
  /**
   * @throws ConsoleException
   */
  public function execute(IArgumentHost $context): int
  {
    $version = self::getVersion() . PHP_EOL;
    $this->logger->log(__CLASS__, $version);
    return Command::SUCCESS;
  }

  /**
   * @return string
   * @throws ConsoleException
   */
  public static function getVersion(): string
  {
    $basePath = Paths::getCliBaseDirectory();
    $versionOutputFile = "$basePath/res/version.txt";

    exec("composer global show assegaiphp/assegai-cli | grep 'versions'");

    if (! file_exists($versionOutputFile))
    {
      throw new ConsoleException('Version output stream error');
    }

    $info = trim(file_get_contents($versionOutputFile));
    Log::getInstance()->log('GET_VERSION', $info);
    return preg_replace('/versions\s*:\s*\*\s*(.*)/', '$1', $info);
  }
}