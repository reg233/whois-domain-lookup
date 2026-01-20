<?php
class ParserAW extends Parser
{
  protected function getUnregisteredRegExp()
  {
    return "/is free/i";
  }

  protected function getRegistrarRegExp()
  {
    return "/registrar:\r\n(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return "/nameservers:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\r\n");
  }
}
