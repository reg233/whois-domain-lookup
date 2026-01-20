<?php

/**
 * Class ParserPLCO
 * 
 * Parses data for the "co.pl" domain extension.
 */
class ParserPLCO extends Parser
{
  protected $dateFormat = "Y.m.d H:i:s";

  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("name");
  }

  protected function getUpdatedDateRegExp()
  {
    return $this->getBaseRegExp("lastmod");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("ns");
  }
}
