<?php

declare(strict_types=1);

class ParserTG extends Parser
{
  protected function getBaseRegExp(string $pattern): string
  {
    return "/(?:$pattern):\.+(.+)/i";
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("name server \(db\)");
  }
}
