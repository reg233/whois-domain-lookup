<?php

declare(strict_types=1);

class ParserSM extends Parser
{
  protected ?string $dateFormat = "d/m/Y";

  protected function getReservedRegExp(): string
  {
    // Conflict with co.ms
    // sm.sm
    return "/reserved domain/i";
  }

  protected function getNameServersRegExp(): string
  {
    return "/dns servers:(.+?)(?=\n\n|$)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
