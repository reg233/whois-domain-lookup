<?php

declare(strict_types=1);

class ParserIT extends Parser
{
  protected string $timezone = "Europe/Rome";

  protected function getReservedRegExp(): string
  {
    // it.it
    return "/status: {13}unassignable/i";
  }

  protected function getUnregisteredRegExp(): string
  {
    return "/status: {13}available/i";
  }

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("registrar\n  organization");
  }

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("web");
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode("/");
  }

  protected function getNameServersRegExp(): string
  {
    return "/nameservers(.+)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
