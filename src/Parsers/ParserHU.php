<?php

declare(strict_types=1);

class ParserHU extends Parser
{
  protected string $timezone = "Europe/Budapest";

  protected function getReservedRegExp(): string
  {
    // Conflict with co.ms
    // ac.hu
    return "/reserved domain/i";
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("name servers");
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode(" ");
  }
}
