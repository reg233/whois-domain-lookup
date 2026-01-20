<?php
class ParserTM extends Parser
{
  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("ns \d");
  }
}
