<?php

declare(strict_types=1);

/**
 * Parser for the ac.uk extension.
 */
class ParserUKAC extends Parser
{
  protected function getBaseRegExp(string $pattern): string
  {
    return "/(?:$pattern):\n(.+)/i";
  }

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("registered by");
  }

  protected function getExpirationDateRegExp(): string
  {
    return $this->getBaseRegExp("renewal date");
  }

  protected function getNameServersRegExp(): string
  {
    return "/servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n", "\t");
  }
}
