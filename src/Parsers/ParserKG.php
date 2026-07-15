<?php

declare(strict_types=1);

class ParserKG extends Parser
{
  protected string $timezone = "Asia/Bishkek";

  protected function getDomainRegExp(): string
  {
    return "/^domain (.+) (?:\(.+\))?$/im";
  }

  protected function getStatusRegExp(): string
  {
    return "/^domain .+ \((.+)\)$/im";
  }

  protected function getNameServersRegExp(): string
  {
    return "/name servers in the listed order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
