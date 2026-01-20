<?php
class ParserEU extends Parser
{
  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar:\n {8}name");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("website");
  }

  protected function getNameServersRegExp()
  {
    return "/name servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
