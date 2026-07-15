<?php

declare(strict_types=1);

class ParserPH extends Parser
{
  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(",");
  }
}
