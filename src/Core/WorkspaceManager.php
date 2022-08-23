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
use Assegai\Cli\Util\Paths;
use Assegai\Cli\Util\Text;

/**
 *
 */
final class WorkspaceManager
{
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
  private ?TemplateEngine $templateEngine = null;

  /**
   * Constructs a WorkspaceManager
   */
  private final function __construct()
  {
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
   * @param string $projectName
   * @param object $args
   * @return void
   * @throws InvalidSchemaException
   * @throws WorkspaceException
   * @throws FileNotFoundException
   */
  public function init(string $projectName, object $args): void
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
      $projectName = Console::prompt(message: "What name would you like to use for the new project?", defaultValue: "assegai-app");
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
      "require-dev" => [
        "codeception/codeception" => "^4.2"
      ],
      "scripts" => [
        "start" => "php -S localhost =>5000 assegai-router.php"
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
        "assegaiphp/core" => "*",
        "vlucas/phpdotenv" => "^5.4",
      ]
    ];

    $assegaiConfig = json_encode($assegaiConfig, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    $composerConfig = json_encode($composerConfig, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

    # Copy template files over to working project path
    $templatePath = Paths::join(Paths::getCliSchematicsDirectory(), 'Project/Files');
    $copyCommand = "cp -r -T $templatePath $this->projectPath";

    if (false === exec($copyCommand) )
    {
      throw new WorkspaceException("Failed to copy template files");
    }

    // TODO: Resolve paths and content
    $this->templateEngine->resolvePath(pathTemplate: $templatePath);
    $args->name = $projectName;
    $this->templateEngine->setArgs($args);
    $viewIndexPath = Paths::join($this->projectPath, 'views', 'index.php');

    if (!file_exists($viewIndexPath))
    {
      throw new FileNotFoundException($viewIndexPath);
    }

    $viewIndexContent = file_get_contents($viewIndexPath);
    $viewIndexContent = $this->templateEngine->resolveContent($viewIndexContent);

    $this->writeFile($viewIndexPath, $viewIndexContent, $this->verbose);

    $assegaiConfigPath = Paths::join($this->projectName, 'assegai.json');
    $this->writeFile($assegaiConfigPath, $assegaiConfig, $this->verbose);

    $composerConfigPath = Paths::join($this->projectName, 'composer.json');
    $this->writeFile($composerConfigPath, $composerConfig, $this->verbose);
  }

  /**
   * @return void
   * @throws FileNotFoundException
   * @throws WorkspaceException
   */
  public function install(): void
  {
    printf(
      "%s%s▹▹▹▸▹%s Installation in progress... ☕%s\n",
      TextStyle::BLINK->value, Color::LIGHT_BLUE, Color::WHITE, Color::RESET
    );

    if (Console::confirm(message: 'Would you like to connect to a database?'))
    {
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
      $databasePassword = Console::promptPassword('Password');
      $databasePort = Console::prompt(message: 'Port', defaultValue: $defaultDatabasePort);
      $databasePort = filter_var($databasePort, FILTER_VALIDATE_INT);

      $newDatabaseConfig = [
        'databases' => [
          $databaseType->value() => [
            $databaseName => [
              'host' => $databaseHost ?? 'localhost',
              'user' => $databaseUser ?? 'root',
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

    $installCommand = "cd $this->projectPath && composer update";

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
          Console::logUpdate(path: $filename, filesize: $result);
        }
        else
        {
          Console::logCreate(path: $filename, filesize: $result);
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
      Console::logCreate($path);
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
}