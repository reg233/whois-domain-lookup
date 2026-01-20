<?php
class ParserBG extends Parser
{
  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp()
  {
    return "/name server information:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
