<?php

declare(strict_types=1);

class ParserBO extends Parser
{
  protected function getStatusRegExp(): string
  {
    return $this->getBaseRegExp("state");
  }

  protected function getStatus(?string $subject = null): array
  {
    // Due to the redundancy of the state, it needs to be extracted from the specified string.
    if (preg_match("/other data(.+)/is", $this->data, $matches)) {
      return parent::getStatus($matches[1]);
    }

    return [];
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("dns\d");
  }
}
