<?php
class ParserMT extends Parser
{
  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("nameserver \d");
  }
}
