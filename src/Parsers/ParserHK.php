<?php
class ParserHK extends Parser
{
  protected function getNameServersRegExp()
  {
    return "/name servers information:(.+?)(?=\n\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
