<?php
class ParserTN extends Parser
{
  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name");
  }

  protected function getNameServers($subject = null)
  {
    // Due to the redundancy of the name, it needs to be extracted from the specified string.
    if (preg_match("/dns servers(.+?)(?=\n\n)/is", $this->data, $matches)) {
      return parent::getNameServers($matches[1]);
    }

    return [];
  }
}
