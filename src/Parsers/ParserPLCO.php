<?php

declare(strict_types=1);

/**
 * Class ParserPLCO
 * 
 * Parses data for the "co.pl" domain extension.
 */
class ParserPLCO extends Parser
{
  protected ?string $dateFormat = "Y.m.d H:i:s";

  protected function getDomainRegExp(): string
  {
    return $this->getBaseRegExp("name");
  }

  protected function getUpdatedDateRegExp(): string
  {
    return $this->getBaseRegExp("lastmod");
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("ns");
  }
}
