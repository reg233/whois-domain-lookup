<?php
class ParserDK extends Parser
{
  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("hostname");
  }
}
