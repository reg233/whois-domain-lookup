<?php
class ParserSM extends Parser
{
  protected $dateFormat = "d/m/Y";

  protected function getReservedRegExp()
  {
    // Conflict with co.ms
    // sm.sm
    return "/reserved domain/i";
  }

  protected function getNameServersRegExp()
  {
    return "/dns servers:(.+?)(?=\n\n|$)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
