<?php
class ParserAM extends Parser
{
  protected function getReservedRegExp()
  {
    // Conflict with ac.me
    // fuck.am, xn--y9a3aq.xn--y9a3aq
    return "/reserved name/i";
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp()
  {
    // name.am and arpinet.am has "(zone signed, x DS records)"
    return "/dns servers(?: \(zone signed, \d ds records?\))?:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }

  protected function getDNSSECSigned()
  {
    if (preg_match("/dns servers \(zone signed, \d ds records?\)/i", $this->data, $matches)) {
      return true;
    }

    return null;
  }
}
