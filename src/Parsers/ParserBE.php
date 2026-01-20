<?php
class ParserBE extends Parser
{
  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar:\r\n\tname");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("website");
  }

  protected function getStatusRegExp()
  {
    return "/Flags:(.+?)(?=\r\n\r\n)/s";
  }

  protected function getNameServersRegExp()
  {
    return "/nameservers:(.*?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\r\n");
  }
}
