<?php
class ParserUK extends Parser
{
  protected function getDomainRegExp()
  {
    return "/domain name:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getRegistrarRegExp()
  {
    return "/registrar:(.+)(?=url)/is";
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("url");
  }

  protected function getStatusRegExp()
  {
    return "/registration status:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getStatus()
  {
    return $this->getStatusFromExplode("\n");
  }

  protected function getNameServersRegExp()
  {
    return "/name servers:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromMultiLine();
  }
}
