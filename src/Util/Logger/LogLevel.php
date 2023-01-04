<?php

namespace Assegai\Cli\Util\Logger;

enum LogLevel: string
{
  case EMERGENCY = \Psr\Log\LogLevel::EMERGENCY;
  case ALERT     = \Psr\Log\LogLevel::ALERT;
  case CRITICAL  = \Psr\Log\LogLevel::CRITICAL;
  case ERROR     = \Psr\Log\LogLevel::ERROR;
  case WARNING   = \Psr\Log\LogLevel::WARNING;
  case NOTICE    = \Psr\Log\LogLevel::NOTICE;
  case INFO      = \Psr\Log\LogLevel::INFO;
  case DEBUG     = \Psr\Log\LogLevel::DEBUG;
}