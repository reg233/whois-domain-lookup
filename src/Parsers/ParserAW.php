<?php

declare(strict_types=1);

class ParserAW extends Parser
{
  protected function getUnregisteredRegExp(): string
  {
    return "/is free/i";
  }

  protected function getRegistrarRegExp(): string
  {
    return "/registrar:\r\n(.+)/i";
  }

  protected function getNameServersRegExp(): string
  {
    return "/nameservers:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\r\n");
  }
}
