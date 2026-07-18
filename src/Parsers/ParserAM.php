<?php

declare(strict_types=1);

class ParserAM extends Parser
{
  protected function getReservedRegExp(): string
  {
    // Conflict with ac.me
    // fuck.am, xn--y9a3aq.xn--y9a3aq
    return "/reserved name/i";
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp(): string
  {
    // name.am and arpinet.am has "(zone signed, x DS records)"
    return "/dns servers(?: \(zone signed, \d ds records?\))?:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }

  protected function getDNSSECSigned(): ?bool
  {
    if (preg_match("/dns servers \(zone signed, \d ds records?\)/i", $this->data)) {
      return true;
    }

    return null;
  }
}
