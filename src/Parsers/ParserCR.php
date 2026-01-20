<?php
class ParserCR extends Parser
{
  protected $timezone = "America/Costa_Rica";

  protected function getUpdatedDate($subject = null)
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
