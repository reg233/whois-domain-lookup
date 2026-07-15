<?php

declare(strict_types=1);

class ParserTT extends Parser
{
  protected function getExpirationDateRegExp(): string
  {
    return "/expiration date: (.+) {6}/i";
  }

  protected function getStatusRegExp(): string
  {
    return "/expiration date: .+ {6}(.+)/i";
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("dns hostnames");
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode(",");
  }
}
