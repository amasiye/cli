<?php

namespace Assegai\Cli\Database;

use Assegai\Cli\Attributes\Repository\Entity;
use Assegai\Cli\Enumerations\DataSourceType;
use Assegai\Cli\Interfaces\IDataSource;
use PDO;
use ReflectionClass;
use ReflectionException;

class DataSource implements IDataSource
{
  protected PDO $connection;

  public function __construct(protected DataSourceOptions $options)
  {
  }

  /**
   * @inheritDoc
   */
  public function setOptions(DataSourceOptions $options): self
  {
    $this->options = $options;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function destroy(): void
  {
    // TODO: Implement destroy() method.
  }

  /**
   * @inheritDoc
   */
  public function initialize(): self
  {
    $this->connection = match ($this->options->type) {
      DataSourceType::MYSQL => DBFactory::getMySQLConnection($this->options->connectionOptions->toArray()),
      DataSourceType::POSTGRESQL => DBFactory::getPostgreSQLConnection($this->options->connectionOptions->toArray()),
      DataSourceType::SQLITE => DBFactory::getSQLiteConnection($this->options->connectionOptions->toArray()),
      DataSourceType::MONGODB => DBFactory::getMongoDbConnection($this->options->connectionOptions->toArray()),
      default => DBFactory::getSQLConnection($this->options->connectionOptions->toArray())
    };
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function synchronize(bool $dropBeforeSync): void
  {
    // TODO: Implement synchronize() method.
  }

  /**
   * @inheritDoc
   */
  public function dropDatabase(): void
  {
    // TODO: Implement dropDatabase() method.
  }

  /**
   * @inheritDoc
   */
  public function runMigrations(object|array $options): array
  {
    // TODO: Implement runMigrations() method.
    return [];
  }

  /**
   * @inheritDoc
   * @throws ReflectionException
   */
  public function hasEntityMetaData(object|string $target): bool
  {
    return !is_numeric($this->getMetaData(target: $target));
  }

  /**
   * @inheritDoc
   * @throws ReflectionException
   */
  public function getMetaData(object|string $target): ?Entity
  {
    $reflection = new ReflectionClass($target);

    $attributes = $reflection->getAttributes(Entity::class);

    foreach ($attributes as $attribute)
    {
      return $attribute->newInstance();
    }

    return null;
  }
}