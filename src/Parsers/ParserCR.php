<?php

declare(strict_types=1);

class ParserCR extends Parser
{
  protected string $timezone = "America/Costa_Rica";

  protected function getUpdatedDate(?string $subject = null): string
  {
    // Some domain names do not have an updated date, such as decathlon.cr.
    // In such cases, the retrieved update date is incorrect,
    // so it needs to be extracted from the specified string.
    if (preg_match("/^(.+?)contact:/is", $this->data, $matches)) {
      return parent::getUpdatedDate($matches[1]);
    }

    return "";
  }
}
