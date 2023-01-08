<?php

namespace Assegai\Cli\Database;

final class DataSourceConnectionOptions
{
  public function __construct(
    public readonly string $name,
    public readonly string $host,
    public readonly string $user,
    public readonly string $password,
    public readonly string $port
  )
  {
  }

  public function toArray(): array
  {
    return [
      'name' => $this->name,
      'host' => $this->host,
      'user' => $this->user,
      'password' => $this->password,
      'port' => $this->port,
    ];
  }
}