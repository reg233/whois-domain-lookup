<?php
class ParserUA extends Parser
{
  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("organization");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("url");
  }

  protected function getStatus($subject = null)
  {
    // Due to the redundancy of the status, it needs to be extracted from the specified string.
    if (preg_match("/^(.+)% registrar:/is", $this->data, $matches)) {
      return parent::getStatus($matches[1]);
    }

    return [];
  }
}
