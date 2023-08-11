<?php

namespace Assegai\Cli\Core;

use Assegai\Cli\Core\Console\Console;
use Assegai\Cli\Core\Console\TermInfo;
use Assegai\Cli\Core\Menus\Menu;
use Assegai\Cli\Core\Menus\MenuItem;
use Assegai\Cli\Enumerations\Color\Color;
use Assegai\Cli\Enumerations\Color\TextStyle;
use Assegai\Cli\Exceptions\FileNotFoundException;
use Assegai\Cli\Exceptions\InvalidSchemaException;
use Assegai\Cli\Exceptions\WorkspaceException;
use Assegai\Cli\Schematics\TemplateEngine;
use Assegai\Cli\Util\Arrays;
use Assegai\Cli\Util\Directory;
use Assegai\Cli\Util\Logger\Log;
use Assegai\Cli\Util\Paths;
use Assegai\Cli\Util\Text;
use Exception;
use Phar;

/**
 * Class WorkspaceManager. Manages the workspace.
 */
final class WorkspaceManager
{
  const LOG_TAG = '[WorkspaceManager]';
  const ERROR_TAG = '[WorkspaceManager Error]';
  /**
   * @var WorkspaceManager|null
   */
  private static ?WorkspaceManager $instance = null;

  /**
   * @var bool
   */
  private bool $verbose = false;

  /**
   * @var string
   */
  private string $projectPath = '';
  /**
   * @var string
   */
  private string $projectName = '';
  /**
   * @var TemplateEngine|null
   */
  protected ?TemplateEngine $templateEngine = null;
  /**
   * @var Log $logger The logger.
   */
  protected Log $logger;

  /**
   * Constructs a WorkspaceManager
   */
  private final function __construct()
  {
    $this->logger = Log::getInstance();
  }

  /**
   * @return static
   */
  public static function getInstance(): self
  {
    if (! self::$instance )
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * @return bool
   */
  public static function hasLocalComposer(): bool
  {
    return file_exists(Paths::join(Paths::getWorkingDirectory(), 'composer.phar'));
  }

  /**
   * @return bool
   */
  public static function hasGlobalComposer(): bool
  {
    return !empty(shell_exec("which composer"));
  }

  /**
   * Initializes the project workspace.
   *
   * @param string $projectName The name of the project.
   * @return void
   * @throws InvalidSchemaException If the project schema is invalid.
   * @throws WorkspaceException If the project could not be initialized.
   * @throws FileNotFoundException If the project schema could not be found.
   */
  public function init(string $projectName): void
  {
    $projectSchemaPath = Paths::join(Paths::getCliSchematicsDirectory(), 'Project', 'schema.php');

    if (! file_exists($projectSchemaPath) )
    {
      $filename = basename($projectSchemaPath);
      throw new WorkspaceException("Failed to load project $filename.");
    }

    $projectSchema = require($projectSchemaPath);

    if (!is_array($projectSchema))
    {
      throw new InvalidSchemaException("Project schema is not an array.");
    }

    $this->templateEngine = new TemplateEngine(schema: $projectSchema);

    if (! $projectName )
    {
      $projectName = Console::prompt(
        message: "What name would you like to use for the new project?",
        defaultValue: "assegai-app"
      );
    }

    $this->projectName = Text::dasherize($projectName);
    $this->projectPath = $this->createDirectory(path: $this->projectName);

    $description = Console::prompt(message: "Description");
    $version = Console::prompt(message: "Version", defaultValue: '0.0.1');

    $projectTypeMenu = new Menu(
      title: '',
      items: [
        new MenuItem(value: 'project'),
        new MenuItem(value: 'library'),
      ]
    );
    $projectType = $projectTypeMenu->prompt(message: 'Project Type', useKeypad: true)->value();

    $assegaiConfig = [
      "name" => $this->projectName,
      "description" => $description,
      "version" => $version,
      "projectType" => $projectType,
      "root" => "",
      "sourceRoot" => "src",
      "scripts" => [
        "test" => "./vendor/bin/phpunit --testdox"
      ],
      "development" => [
        "server" => [
          "host" => "localhost",
          "port" => 5000,
          "openBrowser" => true
        ]
      ]
    ];

    $composerConfig = [
      "name" => 'assegaiphp/' . Text::snakerize($this->projectName),
      "description" => $description,
      "type" => $projectType,
      "scripts" => [
        "start" => "php -S localhost:5000 assegai-router.php",
      ],
      "license" => "MIT",
      "autoload" => [
        "psr-4" => [
          "Assegai\\App\\" => "src/"
        ]
      ],
      "authors" => [],
      "require" => [
        "php" => ">=8.1",
        "ext-pdo" => "*",
        "ext-curl" => "*",
        "vlucas/phpdotenv" => "^5.4",
      ]
    ];

    $assegaiConfig = json_encode($assegaiConfig, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    $composerConfig = json_encode($composerConfig, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

    # Copy template files over to working project path
    $templatePath = Paths::join(Paths::getCliSchematicsDirectory(), 'Project/Files');

    $cliFilename = Paths::getCliBaseDirectory();
    if (is_phar($cliFilename))
    {
      // Extract phar
      if (!$this->copyFilesFromPhar($cliFilename, 'src/Schematics/Project/Files', $this->projectPath))
      {
        throw new WorkspaceException("Failed to copy template files");
      }
    }
    else
    {
      // Do normal copy
      $copyCommand = "cp -r -T $templatePath $this->projectPath";
      if (false === exec($copyCommand) )
      {
        throw new WorkspaceException("Failed to copy template files");
      }
    }

    $assegaiConfigPath = Paths::join($this->projectName, 'assegai.json');
    $this->writeFile($assegaiConfigPath, $assegaiConfig, $this->verbose);

    $composerConfigPath = Paths::join($this->projectName, 'composer.json');
    $this->writeFile($composerConfigPath, $composerConfig, $this->verbose);
  }

  /**
   * Installs the project dependencies.
   *
   * @return void
   * @throws FileNotFoundException If the composer.json file could not be found.
   * @throws WorkspaceException If the project dependencies could not be installed.
   */
  public function install(): void
  {
    printf(
      "%s%s▹▹▹▸▹%s Installation in progress... ☕%s\n",
      TextStyle::BLINK->value, Color::LIGHT_BLUE, Color::WHITE, Color::RESET
    );

    $shouldInstallOrm = Console::confirm(message: 'Would you like to connect to a database?');

    if ($shouldInstallOrm && ! $this->meetsOrmRequirements() )
    {
      Console::error('You must install the PDO extension to connect to a database.');
      $shouldInstallOrm = false;
    }

    if ($shouldInstallOrm)
    {
      $shouldInstallOrm = true;
      $databaseMenu = new Menu(title: '');
      $databaseTypes = $this->getDatabaseTypes(path: Paths::join($this->projectPath, 'config/default.php'));
      foreach ($databaseTypes as $type)
      {
        $databaseMenu->add(new MenuItem(value: $type));
      }
      $databaseType = $databaseMenu->prompt(message: "Which database are you connecting to?", useKeypad: true);

      if (!$databaseType)
      {
        $databaseType = 'mysql';
      }

      $defaultDatabaseName = Text::snakerize($this->projectName);
      $defaultDatabasePort = match($databaseType->value() ?? $databaseType) {
        'mysql' => 3306,
        'pgsql',
        'postgres' => 5432,
        'mongodb' => 27017,
        default => null
      };
      $databaseName = Console::prompt(message: 'What is the database name?', defaultValue: $defaultDatabaseName);
      $databaseHost = Console::prompt(message: 'Host', defaultValue: 'localhost');
      $databaseUser = Console::prompt(message: 'User', defaultValue: 'root');
      $databasePassword = Console::promptPassword();
      $databasePort = Console::prompt(message: 'Port', defaultValue: $defaultDatabasePort);
      $databasePort = filter_var($databasePort, FILTER_VALIDATE_INT);

      $newDatabaseConfig = [
        'databases' => [
          $databaseType->value() => [
            $databaseName => [
              'host' => $databaseHost,
              'user' => $databaseUser,
              'password' => $databasePassword,
              'port' => $databasePort ?? 3306,
            ]
          ]
        ]
      ];

      $configPath = Paths::join($this->projectPath, 'config/default.php');
      $oldDatabaseConfig = require($configPath);
      $databaseConfig = array_merge($oldDatabaseConfig, $newDatabaseConfig);

      $configContent = "<?php\n\nreturn " . Arrays::printArray($databaseConfig) . ';';
      if ( false === file_put_contents($configPath, $configContent) )
      {
        throw new WorkspaceException("Failed to update config/default.php");
      }

      if (! file_exists( Paths::join($this->projectPath, 'src', 'Users') ) )
      {
        $userServiceName = Console::prompt(message: "What is the name of the users resource?", defaultValue: 'Users');
        $command = "cd $this->projectPath && assegai generate resource $userServiceName";

        if ( false === passthru($command) )
        {
          Console::error("Failed to create resource, $userServiceName");
        }
      }
    }

    $installCommand = "cd $this->projectPath && composer --ansi require assegaiphp/core";
    if ($shouldInstallOrm)
    {
      $installCommand .= " && composer --ansi require assegaiphp/orm";
    }

    $dependencyInstallationResult = system(command: $installCommand);

    if (false === $dependencyInstallationResult)
    {
      throw new WorkspaceException(message: 'Failed to install dependencies');
    }

    printf(
      "%s%s\r%s✔%s Installation done! ☕\n\n",
      Console::cursor()::moveUp(return: true),
      Console::eraser()::entireLine(),
      Color::LIGHT_GREEN,
      Color::RESET);

    printf("🚀  Successfully created project %s%s%s\n", Color::LIGHT_GREEN, $this->projectName, Color::RESET);
    printf("👉  Get started with the following commands:\n\n");
    printf("%s$ cd %s%s\n", Color::DARK_WHITE, $this->projectName, Color::RESET);
    printf("%s$ assegai serve %s\n\n\n", Color::DARK_WHITE, Color::RESET);

    $thankYouMessage = [
      sprintf("%s        Thanks for installing Assegai%s 🙏" . PHP_EOL, Color::YELLOW, Color::RESET),
      sprintf("%sPlease consider donating to our open collective%s\n", Color::DARK_WHITE, Color::RESET),
      sprintf("%s    to help us maintain this package.%s\n\n\n", Color::DARK_WHITE, Color::RESET),
    ];

    foreach ($thankYouMessage as $line)
    {
      $lineLength = strlen($line);
      $offset = (TermInfo::windowSize()->width() / 2) - ($lineLength / 2);
      for ($x = 0; $x < $offset; $x++)
      {
        echo ' ';
      }
      echo $line;
    }

    $donationLink = sprintf("🍷  %sDonate: https://opencollective.com/assegai\n\n", Color::RESET);
    $lineLength = strlen($donationLink);
    $offset = (TermInfo::windowSize()->width() / 2) - ($lineLength / 2);
    for ($x = 0; $x < $offset; $x++)
    {
      echo ' ';
    }
    echo $donationLink;
  }

  /**
   * @param string $filename
   * @param mixed $data
   * @param bool $verbose
   * @return bool
   */
  public function writeFile(string $filename, mixed $data, bool $verbose = true): bool
  {
    $isUpdate = file_exists($filename);
    $workingDirectory = Paths::getWorkingDirectory();
    $path = str_starts_with($filename, $workingDirectory)
      ? $filename
      : Paths::join($workingDirectory, $filename);

    $result = file_put_contents($path, $data);

    if (is_numeric($result))
    {
      if ($verbose)
      {
        if ($isUpdate)
        {
          Console::logFileUpdate(path: $filename, newFileSize: $result);
        }
        else
        {
          Console::logFileCreate(path: $filename, newFileSize: $result);
        }
      }

      return true;
    }

    return false;
  }

  /**
   * @param string $path
   * @param bool $verbose
   * @return string
   * @throws WorkspaceException
   */
  public function createDirectory(string $path, bool $verbose = false): string
  {
    $workingDirectory = Paths::getWorkingDirectory();
    $path = Paths::join($workingDirectory, $path);

    if (is_dir($path))
    {
      Console::log("Nothing to do");
      return $path;
    }

    if (! mkdir($path, 0777, true) )
    {
      throw new WorkspaceException("Failed to create directory, $path");
    }

    if ($verbose)
    {
      Console::logFileCreate($path);
    }

    return $path;
  }

  /**
   * @param string $path
   * @return array
   * @throws FileNotFoundException
   */
  public function getDatabaseTypes(string $path): array
  {
    if (! file_exists($path) )
    {
      throw new FileNotFoundException(filename: $path);
    }

    $config = require($path);

    if (isset($config['databases']))
    {
      return array_keys($config['databases']);
    }

    return [];
  }

  /**
   * Copy files from a .phar file to a destination directory.
   *
   * @param string $pharPath The path to the .phar file.
   * @param string $sourceDirectory The directory within the .phar file to copy files from.
   * @param string $destinationDirectory The directory to copy files to.
   *
   * @return bool True if the files were copied successfully, false otherwise.
   * @throws WorkspaceException If the source directory does not exist within the .phar file.
   */
  function copyFilesFromPhar(string $pharPath, string $sourceDirectory, string $destinationDirectory): bool
  {
    # Create a temporary directory.
    $this->logger->log(WorkspaceManager::LOG_TAG,'Creating temporary directory');
    $temporaryDirectory = Paths::join(sys_get_temp_dir(), 'assegai');

    if (false === Directory::create($temporaryDirectory))
    {
      throw new WorkspaceException('Failed to create temporary directory');
    }

    try
    {
      # Extract the contents of the phar to a temporary directory.
      $phar = new Phar($pharPath);
      $phar->extractTo($temporaryDirectory);

      # Copy the files from the temporary directory to the destination directory.
      $sourceDirectoryPath = Paths::join($temporaryDirectory, $sourceDirectory);

      if (Directory::doesNotExist($sourceDirectoryPath))
      {
        throw new WorkspaceException("Source directory ($sourceDirectoryPath) does not exist.");
      }

      $this->logger->log(WorkspaceManager::LOG_TAG,'Copying files from temporary directory to destination directory');
      if (false === `cp -r $sourceDirectoryPath/* $destinationDirectory`)
      {
        throw new WorkspaceException('Failed to copy files from temporary directory to destination directory');
      }
    }
    catch (Exception $exception)
    {
      $this->logger->error(self::ERROR_TAG, $exception->getMessage());
      throw new WorkspaceException($exception);
    }

    # Delete the temporary directory.
    if (false === Directory::delete($temporaryDirectory))
    {
      throw new WorkspaceException('Failed to delete temporary directory');
    }

    return true;
  }

  /**
   * Check if the system meets the requirements for the ORM.
   *
   * @return bool
   */
  private function meetsOrmRequirements(): bool
  {
    printf("\n%sChecking for assegaiphp/orm requirements...%s", Color::LIGHT_BLUE, Color::RESET);

    # Check system for ext-pdo
    if (false === extension_loaded('pdo'))
    {
      Console::error('The ext-pdo extension is required.');
      return false;
    }

    # Check system for ext-curl
    if (false === extension_loaded('curl'))
    {
      Console::error('The ext-curl extension is required.');
      return false;
    }

    # Check system for ext-intl
    if (false === extension_loaded('intl'))
    {
      Console::error('The ext-intl extension is required.');
      return false;
    }

    # Check system for ext-pdo_mysql
    if (false === extension_loaded('pdo_mysql'))
    {
      Console::error('The ext-pdo_mysql extension is required.');
      return false;
    }

    # Check system for ext-pdo_pgsql
    if (false === extension_loaded('pdo_pgsql'))
    {
      Console::error('The ext-pdo_pgsql extension is required.');
      return false;
    }

    # Check system for ext-pdo_sqlite
    if (false === extension_loaded('pdo_sqlite'))
    {
      Console::error('The ext-pdo_sqlite extension is required.');
      return false;
    }

    return true;
  }
}