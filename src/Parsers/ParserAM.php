<?php
class ParserAM extends Parser
{
  protected function getStatus()
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp()
  {
    // name.am and arpinet.am has "(zone signed, x DS records)"
    return "/dns servers(?: \(zone signed, \d DS records?\))?:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromMultiLine();
  }
}
