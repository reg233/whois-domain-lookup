<?php

/**
 * Class ParserUA1
 * 
 * Parses data for the "укр" domain extension.
 */
class ParserUA1 extends Parser
{
  protected function getNameServersRegExp()
  {
    return "/domain servers in listed order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
