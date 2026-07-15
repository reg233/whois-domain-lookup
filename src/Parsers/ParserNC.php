<?php

declare(strict_types=1);

class ParserNC extends Parser
{
  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("domain server \d");
  }
}
