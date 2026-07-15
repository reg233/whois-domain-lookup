<?php

declare(strict_types=1);

class ParserGT extends Parser
{
  protected string $timezone = "America/Guatemala";

  protected function getNameServersRegExp(): string
  {
    return "/servers:(.*?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
