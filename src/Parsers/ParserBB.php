<?php

declare(strict_types=1);

class ParserBB extends Parser
{
  protected string $timezone = "America/Barbados";

  protected function getUnregistered(): bool
  {
    return str_contains($this->data, 'ERROR: Can\'t open file "/home/whois/static/update.txt"');
  }
}
