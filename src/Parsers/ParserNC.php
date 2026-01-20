<?php
class ParserNC extends Parser
{
  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("domain server \d");
  }
}
