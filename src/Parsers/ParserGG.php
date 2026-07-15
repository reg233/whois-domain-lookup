<?php

declare(strict_types=1);

class ParserGG extends Parser
{
  protected ?string $dateFormat = 'jS F Y \a\t H:i:s.u';

  protected string $timezone = "Europe/Guernsey";

  protected function getBaseRegExp(string $pattern): string
  {
    return "/(?:$pattern):(.+?)(?=\n\n)/is";
  }

  protected function getCreationDateRegExp(): string
  {
    return "/registered on (.+)/i";
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode("\n");
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("name servers");
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
