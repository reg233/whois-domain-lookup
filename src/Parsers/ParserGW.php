<?php
class ParserGW extends Parser
{
  protected $dateFormat = "d/m/Y";

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("nameserver \(hostname\)");
  }
}
