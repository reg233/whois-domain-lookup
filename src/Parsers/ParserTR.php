<?php

declare(strict_types=1);

class ParserTR extends Parser
{
  protected function getDomainRegExp(): string
  {
    return $this->getBaseRegExp("\*\* domain name");
  }

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("organization name");
  }

  protected function getNameServersRegExp(): string
  {
    return "/domain servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
