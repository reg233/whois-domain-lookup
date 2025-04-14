<?php
class ParserAW extends Parser
{
  protected function getRegistrarRegExp()
  {
    return "/registrar:\r\n(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return "/nameservers:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromMultiLine();
  }
}
