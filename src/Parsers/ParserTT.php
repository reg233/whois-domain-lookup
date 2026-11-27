<?php

declare(strict_types=1);

class ParserTT extends Parser
{
  protected function getReservedRegExp(): string
  {
    // co.tt
    return "/is reserved/i";
  }

  protected function getUnregisteredRegExp(): string
  {
    return "/is available/i";
  }

  protected function getExpirationDateRegExp(): string
  {
    return "/expiration date: (.+) /i";
  }

  protected function getStatusRegExp(): string
  {
    return "/expiration date: .+ (.+)/i";
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("dns hostnames and ip addresses");
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode(",");
  }
}
