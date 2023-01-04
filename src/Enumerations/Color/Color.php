<?php

namespace Assegai\Cli\Enumerations\Color;

enum Color: string
{
  const BLACK         = "\e[0;30m";
  const RED           = "\e[0;31m";
  const GREEN         = "\e[0;32m";
  const YELLOW        = "\e[0;33m";
  const BLUE          = "\e[0;34m";
  const MAGENTA       = "\e[0;35m";
  const CYAN          = "\e[0;36m";
  const WHITE         = "\e[0;37m";
  const RESET         = "\e[0;0m";
  const LIGHT_BLACK   = "\e[1;30m";
  const LIGHT_RED     = "\e[1;31m";
  const LIGHT_GREEN   = "\e[1;32m";
  const LIGHT_YELLOW  = "\e[1;33m";
  const LIGHT_BLUE    = "\e[1;34m";
  const LIGHT_MAGENTA = "\e[1;35m";
  const LIGHT_CYAN    = "\e[1;36m";
  const LIGHT_WHITE   = "\e[1;37m";
  const LIGHT_RESET   = "\e[1;0m";
  const DARK_BLACK    = "\e[2;30m";
  const DARK_RED      = "\e[2;31m";
  const DARK_GREEN    = "\e[2;32m";
  const DARK_YELLOW   = "\e[2;33m";
  const DARK_BLUE     = "\e[2;34m";
  const DARK_MAGENTA  = "\e[2;35m";
  const DARK_CYAN     = "\e[2;36m";
  const DARK_WHITE    = "\e[2;37m";
  const DARK_RESET    = "\e[2;0m";
}