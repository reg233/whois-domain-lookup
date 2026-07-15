<?php

declare(strict_types=1);

class ParserBG extends Parser
{
  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp(): string
  {
    return "/name server information:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
