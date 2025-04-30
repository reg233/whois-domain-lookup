<?php
class ParserSA extends Parser
{
  protected function getNameServersRegExp()
  {
    return "/name servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
