<?php

declare(strict_types=1);

class ParserRU extends Parser
{
  protected function getStatusRegExp(): string
  {
    return $this->getBaseRegExp("state");
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(",");
  }
}
