<?php

declare(strict_types=1);

class ParserMD extends Parser
{
  protected function getExpirationDateRegExp(): string
  {
    return $this->getBaseRegExp("expire[sd] on");
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(" ");
  }
}
