<?php

declare(strict_types=1);

/**
 * Parser for the za.net extension.
 */
class ParserNETZA extends Parser
{
  protected function getBaseRegExp(string $pattern): string
  {
    return "/(?:$pattern) +:(.+)/i";
  }

  protected function getNameServersRegExp(): string
  {
    return "/domain name servers listed in order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
