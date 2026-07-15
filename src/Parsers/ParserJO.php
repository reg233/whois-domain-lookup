<?php

declare(strict_types=1);

class ParserJO extends Parser
{
  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("(?:primary|secondary) server\d{0,2}");
  }
}
