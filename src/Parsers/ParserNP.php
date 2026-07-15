<?php

declare(strict_types=1);

class ParserNP extends Parser
{
  protected string $timezone = "Asia/Kathmandu";

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("(?:primary|secondary) name server");
  }
}
