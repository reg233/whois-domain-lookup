<?php
class ParserHK extends Parser
{
  protected function getNameServersRegExp()
  {
    return "/name servers information: ?(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromMultiLine();
  }
}
