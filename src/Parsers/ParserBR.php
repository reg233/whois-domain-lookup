<?php

declare(strict_types=1);

class ParserBR extends Parser
{
  protected function getCreationDateISO8601(): ?string
  {
    return $this->getISO8601(explode(" ", $this->creationDate)[0]);
  }
}
