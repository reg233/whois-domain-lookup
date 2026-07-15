<?php

declare(strict_types=1);

class ParserRS extends Parser
{
  protected string $timezone = "Europe/Belgrade";

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("dns");
  }
}
