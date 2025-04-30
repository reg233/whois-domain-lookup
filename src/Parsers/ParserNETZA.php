<?php

/**
 * Class ParserNETZA
 * 
 * Parses data for the "za.net" domain extension.
 */
class ParserNETZA extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern) +:(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return "/domain name servers listed in order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
