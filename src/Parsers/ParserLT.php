<?php

declare(strict_types=1);

class ParserLT extends Parser
{
  protected function getReservedRegExp(): string
  {
    // fuck.lt
    return "/status:\t{3}blocked/i";
  }

  protected function getUnregisteredRegExp(): string
  {
    return "/status:\t{3}available/i";
  }
}
