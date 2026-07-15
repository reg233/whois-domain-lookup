<?php

declare(strict_types=1);

class ParserSK extends Parser
{
  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("registrar:.+\nname:.+\norganization");
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(",");
  }
}
