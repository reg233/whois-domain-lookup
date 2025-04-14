<?php
class ParserPL extends Parser
{
  protected $dateFormat = "Y.m.d H:i:s";

  protected $timezone = "Europe/Warsaw";

  protected function getRegistrarRegExp()
  {
    return "/registrar:\r\n(.+)/i";
  }

  protected function getRegistrarURLRegExp()
  {
    return "/^(https?:\/\/.+)/im";
  }

  protected function getNameServersRegExp()
  {
    return "/nameservers:(.+?)(?=\n\S)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromMultiLine();
  }
}
