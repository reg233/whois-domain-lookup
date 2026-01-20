<?php
class ParserHU extends Parser
{
  protected $timezone = "Europe/Budapest";

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name servers");
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode(" ");
  }
}
