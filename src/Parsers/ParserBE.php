<?php
class ParserBE extends Parser
{
  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar:\r\n\tname");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("registrar:\r\n.+\r\n\twebsite");
  }

  protected function getStatusRegExp()
  {
    return "/flags:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getNameServersRegExp()
  {
    return "/nameservers:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
