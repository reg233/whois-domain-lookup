<?php

declare(strict_types=1);

class ParserFR extends Parser
{
  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("website");
  }

  protected function getStatusRegExp(): string
  {
    return $this->getBaseRegExp("eppstatus");
  }

  protected function getStatus(?string $subject = null): array
  {
    // Due to the redundancy of the eppstatus, it needs to be extracted from the specified string.
    if (preg_match("/^(.+?)source:/is", $this->data, $matches)) {
      return parent::getStatus($matches[1]);
    }

    return [];
  }
}
