<?php
class ParserUK extends Parser
{
  protected function getDomainRegExp()
  {
    return "/domain name:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getRegistrarRegExp()
  {
    return "/registrar:\r\n(.+) \[/i";
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("url");
  }

  protected function getStatusRegExp()
  {
    return "/registration status:(.+?)(?=\r\n\r\n)/is";
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode("\r\n");
  }

  protected function getNameServersRegExp()
  {
    return "/name servers:\r\n {8}(?!no name servers listed\.)(.+?)(?=\r\n\r\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\r\n");
  }
}
