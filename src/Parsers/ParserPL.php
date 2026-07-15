<?php

declare(strict_types=1);

class ParserPL extends Parser
{
  protected ?string $dateFormat = "Y.m.d H:i:s";

  protected string $timezone = "Europe/Warsaw";

  protected function getDomainRegExp(): string
  {
    return "/domain name:(.+\.pl)/i";
  }

  protected function getRegistrarRegExp(): string
  {
    return "/registrar:\r\n(.+)/i";
  }

  protected function getRegistrarURLRegExp(): string
  {
    return "#^(https?://.+)#im";
  }

  protected function getExpirationDateRegExp(): string
  {
    return $this->getBaseRegExp("(?:renewal|expiration) date");
  }

  protected function getNameServersRegExp(): string
  {
    return "/nameservers:(.+?)(?=\r\n\S)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\r\n");
  }
}
