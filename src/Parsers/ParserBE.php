<?php

declare(strict_types=1);

class ParserBE extends Parser
{
  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("registrar:\r\n\tname");
  }

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("website");
  }

  protected function getStatusRegExp(): string
  {
    return "/Flags:(.+?)(?=\r\n\r\n)/s";
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode("\r\n");
  }

  protected function getNameServersRegExp(): string
  {
    return "/nameservers:(.*?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\r\n");
  }

  protected function getDNSSECSignedExtraRegExp(): string
  {
    return "/keys:\r\n(.+)/i";
  }
}
