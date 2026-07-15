<?php

declare(strict_types=1);

/**
 * Class ParserUA1
 * 
 * Parses data for the "укр" domain extension.
 */
class ParserUA1 extends Parser
{
  protected function getNameServersRegExp(): string
  {
    return "/domain servers in listed order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
