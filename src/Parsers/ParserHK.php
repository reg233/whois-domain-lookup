<?php

declare(strict_types=1);

class ParserHK extends Parser
{
  protected function getNameServersRegExp(): string
  {
    return "/name servers information:(.+?)(?=\n\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
