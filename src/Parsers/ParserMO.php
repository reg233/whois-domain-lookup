<?php

declare(strict_types=1);

class ParserMO extends Parser
{
  protected string $timezone = "Asia/Macau";

  protected function getCreationDateRegExp(): string
  {
    return "/record created on (.+)/i";
  }

  protected function getExpirationDateRegExp(): string
  {
    return "/record expires on (.+)/i";
  }

  protected function getNameServersRegExp(): string
  {
    return "/domain name servers:\n -+\n(.+)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
