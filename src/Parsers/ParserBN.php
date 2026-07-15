<?php

declare(strict_types=1);

class ParserBN extends Parser
{
  protected string $timezone = "Asia/Brunei";

  protected function getNameServersRegExp(): string
  {
    return "/name servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
