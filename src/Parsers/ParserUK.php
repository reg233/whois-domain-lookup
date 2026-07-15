<?php

declare(strict_types=1);

class ParserUK extends Parser
{
  protected function getDomainRegExp(): string
  {
    return "/domain name:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getRegistrarRegExp(): string
  {
    return "/registrar:\r\n(.+) \[/i";
  }

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("url");
  }

  protected function getStatusRegExp(): string
  {
    return "/registration status:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode("\r\n");
  }

  protected function getNameServersRegExp(): string
  {
    return "/name servers:\r\n {8}(?!no name servers listed\.)(.+?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\r\n");
  }

  protected function getDNSSECSignedRegExp(): string
  {
    return "/dnssec:\r\n(.+)/i";
  }
}
