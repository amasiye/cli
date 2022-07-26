<?php

namespace Assegai\Cli\Interfaces;

interface IComparable
{
  public function equals(IComparable $other): bool;

  /**
   * Compares one instance to another.
   * @param IComparable $other
   * @return int Returns 1 if the instance is greater than the other, -1 if it's less than and 0 if the two are equal.
   */
  public function compareTo(IComparable $other): int;
}