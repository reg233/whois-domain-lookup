<?php

declare(strict_types=1);

class ParserSI extends Parser
{
  protected function getReservedRegExp(): string
  {
    // Conflict with .nu and .se extension
    // si.si
    return "/is forbidden/i";
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(",");
  }
}
