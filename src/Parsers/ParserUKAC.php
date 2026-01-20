<?php

/**
 * Class ParserUKAC
 * 
 * Parses data for the "ac.uk" domain extension.
 */
class ParserUKAC extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern):\n(.+)/i";
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registered by");
  }

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("renewal date");
  }

  protected function getNameServersRegExp()
  {
    return "/servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n", "\t");
  }
}
