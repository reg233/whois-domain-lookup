<?php
class ParserBO extends Parser
{
  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("state");
  }

  protected function getStatus($subject = null)
  {
    // Due to the redundancy of the state, it needs to be extracted from the specified string.
    if (preg_match("/other data(.+)/is", $this->data, $matches)) {
      return parent::getStatus($matches[1]);
    }

    return [];
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("dns\d");
  }
}
