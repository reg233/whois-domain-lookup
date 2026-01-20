<?php
class ParserBN extends Parser
{
  protected $timezone = "Asia/Brunei";

  protected function getNameServersRegExp()
  {
    return "/name servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
