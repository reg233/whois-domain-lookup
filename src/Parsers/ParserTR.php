<?php
class ParserTR extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/^(?:$pattern)\.*:(.+)/im";
  }

  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("\*\* Domain Name");
  }

  protected function getRegistrarRegExp()
  {
    return "/(?:organization name\t):(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return "/domain servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
