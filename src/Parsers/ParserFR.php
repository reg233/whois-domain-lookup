<?php
class ParserFR extends Parser
{
  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("website");
  }

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("eppstatus");
  }

  protected function getStatus($subject = null)
  {
    // Due to the redundancy of the eppstatus, it needs to be extracted from the specified string.
    if (preg_match("/^(.+?)source:/is", $this->data, $matches)) {
      return parent::getStatus($matches[1]);
    }

    return [];
  }
}
