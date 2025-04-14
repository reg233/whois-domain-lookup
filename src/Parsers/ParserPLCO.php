<?php

/**
 * Class ParserPLCO
 * 
 * Parses data for the "co.pl" domain extension.
 */
class ParserPLCO extends Parser
{
  protected $dateFormat = "Y.m.d H:i:s";

  protected $timezone = "Europe/Warsaw";

  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern)\.*:(.+)/i";
  }

  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("name");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("ns");
  }
}
