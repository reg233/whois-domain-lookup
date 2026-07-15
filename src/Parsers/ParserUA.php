<?php

declare(strict_types=1);

class ParserUA extends Parser
{
  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("organization");
  }

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("url");
  }

  protected function getStatus(?string $subject = null): array
  {
    // Due to the redundancy of the status, it needs to be extracted from the specified string.
    if (preg_match("/^(.+)% registrar:/is", $this->data, $matches)) {
      return parent::getStatus($matches[1]);
    }

    return [];
  }
}
