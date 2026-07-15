<?php

declare(strict_types=1);

class ParserGW extends Parser
{
  protected ?string $dateFormat = "d/m/Y";

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("nameserver \(hostname\)");
  }
}
