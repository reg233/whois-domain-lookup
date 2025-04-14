<?php
class ParserBG extends Parser
{
  protected function getStatus()
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp()
  {
    return "/name server information:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromMultiLine();
  }
}
