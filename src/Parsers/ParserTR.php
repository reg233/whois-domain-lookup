<?php
class ParserTR extends Parser
{
  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("\*\* domain name");
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("organization name");
  }

  protected function getNameServersRegExp()
  {
    return "/domain servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
