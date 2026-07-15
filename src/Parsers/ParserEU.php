<?php

declare(strict_types=1);

class ParserEU extends Parser
{
  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("registrar:\n {8}name");
  }

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("website");
  }

  protected function getNameServersRegExp(): string
  {
    return "/name servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }

  protected function getDNSSECSignedExtraRegExp(): string
  {
    return "/keys:\n(.+)/i";
  }
}
