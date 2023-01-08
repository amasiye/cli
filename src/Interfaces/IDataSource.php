<?php

namespace Assegai\Cli\Interfaces;

use Assegai\Cli\Attributes\Repository\Entity;
use Assegai\Cli\Database\DataSourceOptions;

interface IDataSource
{
  /**
   * Updates current connection options with provided options.
   *
   * @param DataSourceOptions $options
   * @return $this
   */
  public function setOptions(DataSourceOptions $options): self;

  /**
   * Closes connection with the database.
   * Once connection is closed, you cannot use repositories or perform any operations except opening connection again.
   * @return void
   */
  public function destroy(): void;

  /**
   * Performs connection to the database.
   * This method should be called once on application bootstrap.
   * This method not necessarily creates database connection (depend on database type),
   * but it also can set up a connection pool with database to use.
   *
   * @return $this
   */
  public function initialize(): self;

  /**
   * Creates database schema for all entities registered in this connection.
   * Can be used only after connection to the database is established.
   *
   * @param bool $dropBeforeSync If set to true then it drops the database with all its tables and data
   * @return void
   */
  public function synchronize(bool $dropBeforeSync): void;

  /**
   * Drops the database and all its data.
   * Be careful with this method on production since this method will erase all your database tables and their data.
   * Can be used only after connection to the database is established.
   * @return void
   */
  public function dropDatabase(): void;

  /**
   * Runs all pending migrations.
   * Can be used only after connection to the database is established.
   *
   * @param object|array $options
   * @return array
   */
  public function runMigrations(object|array $options): array;

  /**
   * Checks if entity metadata exist for the given entity class, target name or table name.
   * @param string|object $target
   * @return bool Returns true if the target has entity metadata, otherwise false.
   */
  public function hasEntityMetaData(string|object $target): bool;

  /**
   * Gets entity metadata for the given entity class or schema name.
   *
   * @param string|object $target
   * @return Entity|null Returns an instance of the Entity attribute if metadata was found, otherwise null.
   */
  public function getMetaData(string|object $target): ?Entity;
}