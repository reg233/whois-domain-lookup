<?php

declare(strict_types=1);

class ParserMT extends Parser
{
  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("nameserver \d");
  }
}
