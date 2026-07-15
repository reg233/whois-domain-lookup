<?php

declare(strict_types=1);

class ParserCL extends Parser
{
  protected string $timezone = "America/Santiago";

  protected function getCreationDateISO8601(): ?string
  {
    return $this->getISO8601(substr($this->creationDate, 0, 19));
  }

  protected function getExpirationDateISO8601(): ?string
  {
    return $this->getISO8601(substr($this->expirationDate, 0, 19));
  }
}
