<?php

declare(strict_types=1);

/**
 * Class ParserNETZA
 * 
 * Parses data for the "za.net" domain extension.
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
