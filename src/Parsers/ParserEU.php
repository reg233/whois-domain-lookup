<?php
class ParserEU extends Parser
{
  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar:\n {8}name");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("registrar:\n.+\n {8}website");
  }

  protected function getNameServersRegExp()
  {
    return "/name servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
