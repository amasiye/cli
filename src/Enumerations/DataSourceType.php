<?php

namespace Assegai\Cli\Enumerations;

enum DataSourceType: string
{
  case MYSQL = 'mysql';
  case MSSQL = 'mssql';
  case POSTGRESQL = 'pgsql';
  case SQLITE = 'sqlite';
  case MONGODB = 'mongodb';
}
