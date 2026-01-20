<?php
class ParserPL extends Parser
{
  protected $dateFormat = "Y.m.d H:i:s";

  protected $timezone = "Europe/Warsaw";

  protected function getDomainRegExp()
  {
    return "/domain name:(.+\.pl)/i";
  }

  protected function getRegistrarRegExp()
  {
    return "/registrar:\r\n(.+)/i";
  }

  protected function getRegistrarURLRegExp()
  {
    return "#^(https?://.+)#im";
  }

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("(?:renewal|expiration) date");
  }

  protected function getNameServersRegExp()
  {
    return "/nameservers:(.+?)(?=\r\n\S)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\r\n");
  }
}
