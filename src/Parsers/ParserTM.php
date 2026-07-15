<?php

declare(strict_types=1);

class ParserTM extends Parser
{
  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("ns \d");
  }
}
